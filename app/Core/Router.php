<?php

namespace App\Core;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;

class Router
{
    private static ?self $instance = null;
    private Dispatcher $dispatcher;
    private array $routes = [];
    private array $namedRoutes = [];
    private array $middleware = [];

    private function __construct()
    {
        $this->buildRoutes();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function buildRoutes(): void
    {
        $routeFile = base_path('routes/web.php');
        if (file_exists($routeFile)) {
            require $routeFile;
        }
    }

    private string $currentRouteName = '';
    private string $currentPrefix = '';
    private array $currentMiddleware = [];
    private array $pendingMiddleware = [];

    public function get(string $pattern, callable|array|string $handler, array $middleware = []): self
    {
        $this->addRoute('GET', $pattern, $handler, $middleware);
        return $this;
    }

    public function post(string $pattern, callable|array|string $handler, array $middleware = []): self
    {
        $this->addRoute('POST', $pattern, $handler, $middleware);
        return $this;
    }

    public function put(string $pattern, callable|array|string $handler, array $middleware = []): self
    {
        $this->addRoute('PUT', $pattern, $handler, $middleware);
        return $this;
    }

    public function patch(string $pattern, callable|array|string $handler, array $middleware = []): self
    {
        $this->addRoute('PATCH', $pattern, $handler, $middleware);
        return $this;
    }

    public function delete(string $pattern, callable|array|string $handler, array $middleware = []): self
    {
        $this->addRoute('DELETE', $pattern, $handler, $middleware);
        return $this;
    }

    public function any(string $pattern, callable|array|string $handler, array $middleware = []): self
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->addRoute($method, $pattern, $handler, $middleware);
        }
        return $this;
    }

    public function middleware(array $middleware): self
    {
        $this->pendingMiddleware = array_merge($this->pendingMiddleware, $middleware);
        return $this;
    }

    public function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $previousPrefix = $this->getCurrentPrefix();
        $this->setCurrentPrefix($previousPrefix . $prefix);
        $previousMiddleware = $this->getCurrentMiddleware();
        $this->setCurrentMiddleware(array_merge($previousMiddleware, $middleware));
        $callback($this);
        $this->setCurrentPrefix($previousPrefix);
        $this->setCurrentMiddleware($previousMiddleware);
    }

    public function name(string $name): self
    {
        $this->currentRouteName = $name;
        return $this;
    }

    private string $currentRouteName = '';
    private string $currentPrefix = '';
    private array $currentMiddleware = [];

    private function getCurrentPrefix(): string
    {
        return $this->currentPrefix;
    }

    private function setCurrentPrefix(string $prefix): void
    {
        $this->currentPrefix = $prefix;
    }

    private function getCurrentMiddleware(): array
    {
        return $this->currentMiddleware;
    }

    private function setCurrentMiddleware(array $middleware): void
    {
        $this->currentMiddleware = $middleware;
    }

    private function addRoute(string $method, string $pattern, callable|array|string $handler, array $middleware = []): void
    {
        $fullPattern = $this->currentPrefix . $pattern;
        $fullMiddleware = array_merge($this->currentMiddleware, $this->pendingMiddleware, $middleware);
        $this->pendingMiddleware = [];
        $name = $this->currentRouteName;
        $this->currentRouteName = '';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $fullPattern,
            'handler' => $handler,
            'middleware' => $fullMiddleware,
            'name' => $name,
        ];

        if ($name) {
            $this->namedRoutes[$name] = $fullPattern;
        }
    }

    public function dispatch(string $uri, string $method): void
    {
        $request = ServerRequestFactory::fromGlobals();
        $uri = $request->getUri()->getPath();

        $dispatcher = $this->getDispatcher();
        $routeInfo = $dispatcher->dispatch($method, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                abort(404);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                abort(405);
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                $route = $this->findRoute($method, $uri);

                $middlewares = $route ? $route['middleware'] : [];

                if (!in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
                    $csrfExcluded = ['/api/query', '/api/challenge/submit'];
                    if (!in_array($uri, $csrfExcluded)) {
                        array_unshift($middlewares, 'csrf');
                    }
                }

                if (!$this->runMiddleware($middlewares, $request)) {
                    return;
                }

                $response = $this->callHandler($handler, $vars, $request);

                if ($response instanceof Response) {
                    $this->sendResponse($response);
                }
                break;
        }
    }

    private function findRoute(string $method, string $uri): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            $pattern = $this->patternToRegex($route['pattern']);
            if (preg_match($pattern, $uri, $matches)) {
                return $route;
            }
        }
        return null;
    }

    private function patternToRegex(string $pattern): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $pattern . '$#';
    }

    private function runMiddleware(array $middleware, \Psr\Http\Message\ServerRequestInterface $request): bool
    {
        foreach ($middleware as $mw) {
            if (is_string($mw)) {
                [$name, $param] = array_pad(explode(':', $mw, 2), 2, null);
                $class = "App\\Middleware\\" . ucfirst($name) . "Middleware";
                if (class_exists($class)) {
                    $instance = new $class();
                    if (!$instance->handle($request, $param)) {
                        return false;
                    }
                }
            } elseif (is_callable($mw)) {
                if (!$mw($request)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function callHandler(callable|array|string $handler, array $vars, \Psr\Http\Message\ServerRequestInterface $request): Response
    {
        if (is_string($handler) && str_contains($handler, '@')) {
            [$controllerClass, $method] = explode('@', $handler);
            $controllerClass = "App\\Controllers\\$controllerClass";
            if (!class_exists($controllerClass)) {
                abort(500, "Controller $controllerClass not found");
            }
            $controller = new $controllerClass();
            return $controller->$method($vars, $request);
        }

        if (is_callable($handler)) {
            return $handler($vars, $request);
        }

        abort(500, 'Invalid route handler');
    }

    private function getDispatcher(): Dispatcher
    {
        if (!isset($this->dispatcher)) {
            $this->dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
                foreach ($this->routes as $route) {
                    $r->addRoute($route['method'], $route['pattern'], $route['handler']);
                }
            });
        }
        return $this->dispatcher;
    }

    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            return '/';
        }

        $url = $this->namedRoutes[$name];
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', (string)$value, $url);
        }
        return $url;
    }

    private function sendResponse(Response $response): void
    {
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }
        http_response_code($response->getStatusCode());
        echo $response->getBody();
    }
}