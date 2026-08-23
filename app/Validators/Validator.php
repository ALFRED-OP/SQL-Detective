<?php

namespace App\Validators;

class Validator
{
    private array $data;
    private array $rules;
    private array $messages;
    private array $errors = [];
    private array $validated = [];

    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    public function validate(): array
    {
        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;
            $ruleList = is_string($rules) ? explode('|', $rules) : $rules;

            foreach ($ruleList as $rule) {
                [$ruleName, $parameters] = $this->parseRule($rule);
                $method = 'validate' . ucfirst($ruleName);

                if (method_exists($this, $method)) {
                    $result = $this->$method($field, $value, $parameters);
                    if (!$result) {
                        $this->addError($field, $ruleName, $parameters);
                        break;
                    }
                }
            }

            if (!isset($this->errors[$field])) {
                $this->validated[$field] = $value;
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }

        return $this->validated;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function failed(): bool
    {
        return !empty($this->errors);
    }

    private function parseRule(string $rule): array
    {
        if (str_contains($rule, ':')) {
            [$name, $params] = explode(':', $rule, 2);
            return [$name, explode(',', $params)];
        }
        return [$rule, []];
    }

    private function addError(string $field, string $rule, array $parameters): void
    {
        $key = "$field.$rule";
        $message = $this->messages[$key]
            ?? $this->messages["$field.*"]
            ?? $this->messages[$rule]
            ?? $this->defaultMessage($field, $rule, $parameters);

        $this->errors[$field][] = $message;
    }

    private function defaultMessage(string $field, string $rule, array $parameters): string
    {
        $fieldName = ucfirst(str_replace('_', ' ', $field));
        return match ($rule) {
            'required' => "$fieldName is required.",
            'email' => "$fieldName must be a valid email address.",
            'min' => "$fieldName must be at least {$parameters[0]} characters.",
            'max' => "$fieldName must not exceed {$parameters[0]} characters.",
            'between' => "$fieldName must be between {$parameters[0]} and {$parameters[1]} characters.",
            'confirmed' => "$fieldName confirmation does not match.",
            'unique' => "$fieldName has already been taken.",
            'exists' => "$fieldName is invalid.",
            'in' => "$fieldName must be one of: " . implode(', ', $parameters),
            'not_in' => "$fieldName must not be one of: " . implode(', ', $parameters),
            'alpha' => "$fieldName must contain only letters.",
            'alpha_num' => "$fieldName must contain only letters and numbers.",
            'alpha_dash' => "$fieldName must contain only letters, numbers, dashes, and underscores.",
            'numeric' => "$fieldName must be a number.",
            'integer' => "$fieldName must be an integer.",
            'boolean' => "$fieldName must be true or false.",
            'date' => "$fieldName must be a valid date.",
            'after' => "$fieldName must be after {$parameters[0]}.",
            'before' => "$fieldName must be before {$parameters[0]}.",
            'regex' => "$fieldName format is invalid.",
            'ip' => "$fieldName must be a valid IP address.",
            'url' => "$fieldName must be a valid URL.",
            'file' => "$fieldName must be a file.",
            'image' => "$fieldName must be an image.",
            'mimes' => "$fieldName must be a file of type: " . implode(', ', $parameters),
            'size' => "$fieldName must not exceed {$parameters[0]} KB.",
            'password' => "$fieldName must be at least 8 characters with uppercase, lowercase, and number.",
            default => "$fieldName is invalid.",
        };
    }

    private function validateRequired(string $field, mixed $value, array $params): bool
    {
        if (is_array($value)) {
            return count($value) > 0;
        }
        return $value !== null && $value !== '';
    }

    private function validateEmail(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateMin(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        $min = (int)($params[0] ?? 0);
        if (is_string($value)) return mb_strlen($value) >= $min;
        if (is_array($value)) return count($value) >= $min;
        if (is_numeric($value)) return $value >= $min;
        return false;
    }

    private function validateMax(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        $max = (int)($params[0] ?? PHP_INT_MAX);
        if (is_string($value)) return mb_strlen($value) <= $max;
        if (is_array($value)) return count($value) <= $max;
        if (is_numeric($value)) return $value <= $max;
        return false;
    }

    private function validateBetween(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        $min = (int)($params[0] ?? 0);
        $max = (int)($params[1] ?? PHP_INT_MAX);
        if (is_string($value)) {
            $len = mb_strlen($value);
            return $len >= $min && $len <= $max;
        }
        if (is_array($value)) {
            $count = count($value);
            return $count >= $min && $count <= $max;
        }
        if (is_numeric($value)) {
            return $value >= $min && $value <= $max;
        }
        return false;
    }

    private function validateConfirmed(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        $confirmField = $field . '_confirmation';
        return $value === ($this->data[$confirmField] ?? '');
    }

    private function validateUnique(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        [$table, $column] = $params;
        $column = $column ?? $field;
        $db = \App\Core\Application::getInstance()->db();
        $sql = "SELECT COUNT(*) FROM $table WHERE $column = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$value]);
        return $stmt->fetchColumn() === 0;
    }

    private function validateExists(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        [$table, $column] = $params;
        $column = $column ?? 'id';
        $db = \App\Core\Application::getInstance()->db();
        $sql = "SELECT COUNT(*) FROM $table WHERE $column = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$value]);
        return $stmt->fetchColumn() > 0;
    }

    private function validateIn(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return in_array($value, $params, true);
    }

    private function validateNotIn(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return !in_array($value, $params, true);
    }

    private function validateAlpha(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return ctype_alpha($value);
    }

    private function validateAlphaNum(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return ctype_alnum($value);
    }

    private function validateAlphaDash(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return preg_match('/^[a-zA-Z0-9_-]+$/', $value) === 1;
    }

    private function validateNumeric(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return is_numeric($value);
    }

    private function validateInteger(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function validateBoolean(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true);
    }

    private function validateDate(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        $format = $params[0] ?? 'Y-m-d';
        $date = \DateTime::createFromFormat($format, $value);
        return $date && $date->format($format) === $value;
    }

    private function validateAfter(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        $date = new \DateTime($value);
        $compare = new \DateTime($params[0]);
        return $date > $compare;
    }

    private function validateBefore(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        $date = new \DateTime($value);
        $compare = new \DateTime($params[0]);
        return $date < $compare;
    }

    private function validateRegex(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return preg_match($params[0], $value) === 1;
    }

    private function validateIp(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    private function validateUrl(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function validatePassword(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        if (strlen($value) < 8) return false;
        if (!preg_match('/[A-Z]/', $value)) return false;
        if (!preg_match('/[a-z]/', $value)) return false;
        if (!preg_match('/[0-9]/', $value)) return false;
        return true;
    }
}

class ValidationException extends \Exception
{
    public array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('Validation failed', 422);
    }
}