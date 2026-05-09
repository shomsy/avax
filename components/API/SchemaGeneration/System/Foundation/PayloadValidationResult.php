<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Foundation;

/**
 * PayloadValidationResult — result of validating a payload against a schema.
 */
final readonly class PayloadValidationResult
{
    /**
     * @param list<string> $errors
     */
    public function __construct(
        public bool $valid,
        public array $errors = [],
    ) {}

    public static function valid() : self
    {
        return new self(valid: true);
    }

    /**
     * @param list<string> $errors
     */
    public static function invalid(array $errors) : self
    {
        return new self(valid: false, errors: $errors);
    }
}
