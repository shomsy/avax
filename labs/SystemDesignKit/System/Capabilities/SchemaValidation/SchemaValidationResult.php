<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\SchemaValidation;

/**
 * Result of a schema validation operation.
 *
 * @experimental V3 labs
 */
final readonly class SchemaValidationResult
{
    /**
     * @param list<string> $errors
     */
    public function __construct(
        public bool   $valid,
        public array  $errors,
        public string $schema,
        public string $target,
    ) {}

    /**
     * Create a passing result.
     *
     * @param string $schema Schema name that was validated against.
     * @param string $target Target file that was validated.
     */
    public static function pass(string $schema, string $target) : self
    {
        return new self(valid: true, errors: [], schema: $schema, target: $target);
    }

    /**
     * Create a failing result with errors.
     *
     * @param list<string> $errors
     * @param string       $schema Schema name that was validated against.
     * @param string       $target Target file that was validated.
     */
    public static function fail(array $errors, string $schema, string $target) : self
    {
        return new self(valid: false, errors: $errors, schema: $schema, target: $target);
    }
}
