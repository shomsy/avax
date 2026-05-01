<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Capabilities\Rules;

/**
 * Full-featured Validator with support for common validation rules.
 *
 * Rules are specified as pipe-separated strings or arrays:
 *   'email' => 'required|email|max:255'
 *   'age'   => ['required', 'integer', 'min:18', 'max:120']
 */
final class Validator
{
    private array $data = [];

    private array $rules = [];

    /** @var array<string, list<string>> */
    private array $errors = [];

    /** @var array<string, string> Custom error messages */
    private array $messages = [];

    public function setData(array $data) : self
    {
        $this->data = $data;

        return $this;
    }

    public function setRules(array $rules) : self
    {
        $this->rules = $rules;

        return $this;
    }

    public function setMessages(array $messages) : self
    {
        $this->messages = $messages;

        return $this;
    }

    public function validate() : ValidationFailure
    {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleSet) {
            $rules = is_array($ruleSet) ? $ruleSet : explode('|', (string) $ruleSet);
            $value = $this->getValue($field);

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return new ValidationFailure($this->errors);
    }

    public static function make(array $data, array $rules, array $messages = []) : ValidationFailure
    {
        return (new self())
            ->setData($data)
            ->setRules($rules)
            ->setMessages($messages)
            ->validate();
    }

    private function getValue(string $field) : mixed
    {
        if (str_contains($field, '.')) {
            $keys = explode('.', $field);
            $value = $this->data;

            foreach ($keys as $key) {
                if (! is_array($value) || ! array_key_exists($key, $value)) {
                    return null;
                }

                $value = $value[$key];
            }

            return $value;
        }

        return $this->data[$field] ?? null;
    }

    private function applyRule(string $field, mixed $value, string $rule) : void
    {
        // Parse rule and parameters
        $parts  = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $params = isset($parts[1]) ? explode(',', $parts[1]) : [];

        // Skip validation if field is nullable and value is null/empty
        if ($ruleName !== 'required' && $ruleName !== 'nullable' && ($value === null || $value === '')) {
            // Check if nullable rule is present
            $fieldRules = is_array($this->rules[$field]) ? $this->rules[$field] : explode('|', (string) $this->rules[$field]);
            if (in_array('nullable', $fieldRules, true)) {
                return;
            }

            // If no value and not explicitly nullable, skip non-required rules
            if (! in_array('required', $fieldRules, true)) {
                return;
            }
        }

        $result = match ($ruleName) {
            'required' => $this->validateRequired($value),
            'string'   => $this->validateString($value),
            'integer'  => $this->validateInteger($value),
            'numeric'  => $this->validateNumeric($value),
            'email'    => $this->validateEmail($value),
            'min'      => $this->validateMin($value, (int) ($params[0] ?? 0)),
            'max'      => $this->validateMax($value, (int) ($params[0] ?? 0)),
            'between'  => $this->validateBetween($value, (int) ($params[0] ?? 0), (int) ($params[1] ?? 0)),
            'in'       => $this->validateIn($value, $params),
            'array'    => $this->validateArray($value),
            'nullable' => true,
            'url'      => $this->validateUrl($value),
            'uuid'     => $this->validateUuid($value),
            'date'     => $this->validateDate($value),
            'bool', 'boolean' => $this->validateBool($value),
            'regex'    => $this->validateRegex($value, $params[0] ?? ''),
            default    => true,
        };

        if (! $result) {
            $this->addError($field, $ruleName, $params);
        }
    }

    private function validateRequired(mixed $value) : bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        return $value !== [];
    }

    private function validateString(mixed $value) : bool
    {
        return is_string($value);
    }

    private function validateInteger(mixed $value) : bool
    {
        return is_int($value) || (is_string($value) && filter_var($value, FILTER_VALIDATE_INT) !== false);
    }

    private function validateNumeric(mixed $value) : bool
    {
        return is_numeric($value);
    }

    private function validateEmail(mixed $value) : bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateMin(mixed $value, int $min) : bool
    {
        if (is_string($value)) {
            return strlen($value) >= $min;
        }

        if (is_numeric($value)) {
            return $value >= $min;
        }

        if (is_array($value)) {
            return count($value) >= $min;
        }

        return false;
    }

    private function validateMax(mixed $value, int $max) : bool
    {
        if (is_string($value)) {
            return strlen($value) <= $max;
        }

        if (is_numeric($value)) {
            return $value <= $max;
        }

        if (is_array($value)) {
            return count($value) <= $max;
        }

        return false;
    }

    private function validateBetween(mixed $value, int $min, int $max) : bool
    {
        if (is_string($value)) {
            $len = strlen($value);

            return $len >= $min && $len <= $max;
        }

        if (is_numeric($value)) {
            return $value >= $min && $value <= $max;
        }

        if (is_array($value)) {
            $count = count($value);

            return $count >= $min && $count <= $max;
        }

        return false;
    }

    private function validateIn(mixed $value, array $allowed) : bool
    {
        return in_array($value, $allowed, true);
    }

    private function validateArray(mixed $value) : bool
    {
        return is_array($value);
    }

    private function validateUrl(mixed $value) : bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function validateUuid(mixed $value) : bool
    {
        if (! is_string($value)) {
            return false;
        }

        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
    }

    private function validateDate(mixed $value) : bool
    {
        if (! is_string($value)) {
            return false;
        }

        return strtotime($value) !== false;
    }

    private function validateBool(mixed $value) : bool
    {
        return in_array($value, [true, false, 0, 1, '0', '1'], true);
    }

    private function validateRegex(mixed $value, string $pattern) : bool
    {
        return is_string($value) && preg_match($pattern, $value) === 1;
    }

    private function addError(string $field, string $rule, array $params) : void
    {
        $message = $this->getErrorMessage($field, $rule, $params);
        $this->errors[$field][] = $message;
    }

    private function getErrorMessage(string $field, string $rule, array $params) : string
    {
        // Check custom message
        $key = sprintf('%s.%s', $field, $rule);

        return $this->messages[$key] ?? match ($rule) {
            'required' => sprintf('The %s field is required.', $field),
            'string'   => sprintf('The %s field must be a string.', $field),
            'integer'  => sprintf('The %s field must be an integer.', $field),
            'numeric'  => sprintf('The %s field must be a number.', $field),
            'email'    => sprintf('The %s field must be a valid email address.', $field),
            'min'      => sprintf('The %s field must be at least %s.', $field, $params[0]),
            'max'      => sprintf('The %s field must not exceed %s.', $field, $params[0]),
            'between'  => sprintf('The %s field must be between %s and %s.', $field, $params[0], $params[1]),
            'in'       => sprintf('The %s field must be one of: ', $field) . implode(', ', $params),
            'array'    => sprintf('The %s field must be an array.', $field),
            'url'      => sprintf('The %s field must be a valid URL.', $field),
            'uuid'     => sprintf('The %s field must be a valid UUID.', $field),
            'date'     => sprintf('The %s field must be a valid date.', $field),
            'bool', 'boolean' => sprintf('The %s field must be true or false.', $field),
            'regex'    => sprintf('The %s field format is invalid.', $field),
            default    => sprintf('The %s field is invalid.', $field),
        };
    }
}

/**
 * ValidationFailure DTO - represents the result of a validation operation.
 */
final readonly class ValidationFailure
{
    /** @param array<string, list<string>> $errors */
    public function __construct(
        public array $errors = [],
    ) {}

    public function fails() : bool
    {
        return $this->errors !== [];
    }

    public function passes() : bool
    {
        return $this->errors === [];
    }

    public function hasError(string $field) : bool
    {
        return isset($this->errors[$field]);
    }

    public function getError(string $field) : ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /** @return list<string> */
    public function getErrors(string $field) : array
    {
        return $this->errors[$field] ?? [];
    }

    /** @return array<string, list<string>> */
    public function all() : array
    {
        return $this->errors;
    }

    public function first() : ?string
    {
        foreach ($this->errors as $error) {
            return $error[0];
        }

        return null;
    }
}
