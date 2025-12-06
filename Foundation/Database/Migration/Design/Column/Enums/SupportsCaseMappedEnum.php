<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Column\Enums;

use BackedEnum;
use LogicException;
use ValueError;

/**
 * A trait that provides sophisticated case-mapped enum support with DSL capabilities.
 *
 * This trait enhances backed enums with case-insensitive mapping functionality,
 * supporting both direct enum value/name matching and custom DSL aliases.
 *
 * @template T of BackedEnum
 *
 * @author AI Assistant <ai@example.com>
 * @since  1.0.0
 */
trait SupportsCaseMappedEnum
{
    /**
     * Error message for empty or whitespace-only input validation.
     *
     * @var string Constant representing the error message for empty input scenarios
     */
    private const string ERROR_EMPTY_INPUT = "Enum mapping input cannot be empty or whitespace.";

    /**
     * Error message template for invalid alias mapping scenarios.
     *
     * @var string Constant representing the error message for invalid alias configurations
     */
    private const string ERROR_INVALID_ALIAS = 'Invalid alias mapping for "%s". Expected instance of %s, got %s.';

    /**
     * Error message template for unmatched enum cases.
     *
     * @var string Constant representing the error message when no matching enum case is found
     */
    private const string ERROR_NO_MATCH = 'No matching enum case found for "%s". Valid inputs: [%s]';

    /**
     * Determines if the provided input string can be mapped to an enum case.
     *
     * @param string $input The input string to validate for mapping possibility
     *
     * @return bool True if mapping is possible, false otherwise
     */
    public static function canMap(string $input) : bool
    {
        return static::tryMap(input: $input) !== null;
    }

    /**
     * Attempts to map the input string to an enum case, returning null on failure.
     *
     * @param string $input The input string to attempt mapping
     *
     * @return static|null The mapped enum case or null if mapping fails
     */
    public static function tryMap(string $input) : static|null
    {
        try {
            return static::map(input: $input);
        } catch (ValueError) {
            return null;
        }
    }

    /**
     * Maps the input string to an enum case or throws an exception on failure.
     *
     * @param string $input The input string to map to an enum case
     *
     * @return static The successfully mapped enum case
     * @throws ValueError When mapping fails or input is invalid
     */
    public static function map(string $input) : static
    {
        // Validate input for emptiness
        if (trim($input) === '') {
            throw new ValueError(message: self::ERROR_EMPTY_INPUT);
        }

        // Normalize input for case-insensitive comparison
        $normalized = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input)); // camelCase → snake_case → lower

        // Attempt to find a match through various strategies
        $matchedCase = self::findMatchInAliases($normalized)
                       ?? self::findMatchByValue($normalized)
                          ?? self::findMatchByName($normalized);

        if ($matchedCase !== null) {
            return $matchedCase;
        }

        // No match found, throw a detailed exception
        throw new ValueError(
            message: sprintf(
                         self::ERROR_NO_MATCH,
                         $input,
                         self::getValidInputsString($normalized)
                     )
        );
    }

    /**
     * Attempts to find a matching enum case through configured aliases.
     *
     * @param string $normalized The normalized input string
     *
     * @return static|null The matched enum case or null if no match found
     * @throws LogicException When alias mapping is invalid
     */
    private static function findMatchInAliases(string $normalized) : static|null
    {
        // Check if DSL aliases are supported
        if (! method_exists(static::class, 'dslAliases')) {
            return null;
        }

        $aliases = static::dslAliases();

        // Check if normalized input exists in aliases
        if (! array_key_exists($normalized, $aliases)) {
            return null;
        }

        $aliasTarget = $aliases[$normalized];

        // Validate alias target type
        if (! ($aliasTarget instanceof static)) {
            throw new LogicException(
                message: sprintf(
                             self::ERROR_INVALID_ALIAS,
                             $normalized,
                             static::class,
                             get_debug_type($aliasTarget)
                         )
            );
        }

        return $aliasTarget;
    }

    /**
     * Provides DSL aliases for enum cases. Override this method to define custom mappings.
     *
     * @return array<string, static> Array of alias => enum case mappings
     */
    protected static function dslAliases() : array
    {
        return [];
    }

    /**
     * Attempts to find a matching enum case by its value.
     *
     * @param string $normalized The normalized input string
     *
     * @return static|null The matched enum case or null if no match found
     */
    private static function findMatchByValue(string $normalized) : static|null
    {
        foreach (static::cases() as $case) {
            if (strtolower($case->value) === $normalized) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Attempts to find a matching enum case by its name.
     *
     * @param string $normalized The normalized input string
     *
     * @return static|null The matched enum case or null if no match found
     */
    private static function findMatchByName(string $normalized) : static|null
    {
        foreach (static::cases() as $case) {
            if (strtolower($case->name) === $normalized) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Generates a string of all valid input values for error messaging.
     *
     * @param string $normalized The normalized input string (unused but kept for consistency)
     *
     * @return string Comma-separated list of valid inputs
     */
    protected static function getValidInputsString(string $normalized) : string
    {
        $cases   = static::cases();
        $aliases = method_exists(static::class, 'dslAliases') ? static::dslAliases() : [];

        $valid = array_merge(
            array_keys($aliases),
            array_map(static fn($c) => strtolower($c->value), $cases),
            array_map(static fn($c) => strtolower($c->name), $cases)
        );

        return implode(', ', array_unique($valid));
    }
}