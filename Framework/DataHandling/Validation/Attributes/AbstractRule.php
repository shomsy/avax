<?php

namespace Gemini\DataHandling\Validation\Attributes;

use Gemini\DataHandling\Validation\Attributes\Contracts\RuleValidator;

abstract class AbstractRule implements RuleValidator
{
    use RuleHelpers;

    /**
     * Common error thrower.
     */
    protected function fail(string $message, string $property) : never
    {
        throw new ValidationException("Validation failed on '{$property}': {$message}");
    }
}
