<?php

namespace App\Core;

class View
{
    private string $viewsPath;
    private array $globals = [];
    private string $layout = 'layouts.app';

    public function __construct()
    {
        $this->viewsPath = base_path('views/');
        $this->share([
            'appName' => config('app.name'),
            'csrf_token' => csrf_token(),
            'auth' => auth_user(),
            'flash' => get_flash('message') ?? get_flash('error'),
        ]);
    }

    public function share(array $data): self
    {
        $this->globals = array_merge($this->globals, $data);
        return $this;
    }

    public function layout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    public function render(string $name, array $data = []): string
    {
        $data = array_merge($this->globals, $data);
        $viewFile = $this->viewsPath . str_replace('.', '/', $name) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View [$name] not found at $viewFile");
        }

        ob_start();
        extract($data, EXTR_SKIP);
        require $viewFile;
        $content = ob_get_clean();

        if ($this->layout && $name !== $this->layout) {
            $layoutFile = $this->viewsPath . str_replace('.', '/', $this->layout) . '.php';
            if (file_exists($layoutFile)) {
                ob_start();
                extract(['content' => $content] + $data, EXTR_SKIP);
                require $layoutFile;
                return ob_get_clean();
            }
        }

        return $content;
    }

    public function component(string $name, array $data = []): string
    {
        return $this->render("components.$name", $data);
    }
}