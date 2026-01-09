<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Utils;

use Avax\Container\Features\Operate\Config\ContainerConfig;

/**
 * Utility class providing string manipulation functions for dependency injection container operations.
 *
 * This static utility class offers safe, predictable, and performant string operations
 * essential for the container's functionality. It handles class name transformations,
 * namespace extraction, identifier normalization, and pattern matching operations
 * used throughout the container's analysis and resolution phases.
 *
 * OPERATIONAL CONTEXT:
 * - Class name conversion for service ID generation
 * - Namespace extraction for autowiring and validation
 * - Cache key sanitization for filesystem safety
 * - Pattern matching for configuration and security checks
 *
 * DESIGN PRINCIPLES:
 * - Pure functions with no side effects
 * - Consistent behavior across PHP versions
 * - Performance optimized for container hot paths
 * - Null-safe operations with predictable results
 *
 * USAGE SCENARIOS:
 * ```php
 * // Service ID generation
 * $id = StrTools::classToId('App\\Services\\UserService');
 * // Result: 'App.Services.UserService'
 *
 * // Namespace validation
 * $namespace = StrTools::extractNamespace('App\\Services\\UserService');
 * // Result: 'App\\Services'
 *
 * // Cache key sanitization
 * $key = StrTools::toCacheKey('complex/key:with@symbols');
 * // Result: 'complex_key_with_symbols'
 * ```
 *
 * PERFORMANCE CHARACTERISTICS:
 * - All operations are O(n) where n is string length
 * - No heap allocations for primitive operations
 * - Regex operations cached for repeated use
 * - Memory efficient for large codebase processing
 *
 * THREAD SAFETY:
 * All methods are static and stateless, fully thread-safe.
 *
 * @package Avax\Container\Core\Utils
 * @see     ContainerConfig For configuration string processing
 */
final class StrTools
{
    /**
     * Converts a fully qualified class name to a container service identifier.
     *
     * Transforms PHP namespace separators and directory separators into dots,
     * creating a flat, filesystem-safe identifier suitable for container storage
     * and resolution. Leading dots are trimmed to ensure clean identifiers.
     *
     * CONVERSION RULES:
     * - Namespace separators (\) become dots (.)
     * - Directory separators (/) become dots (.)
     * - Leading dots are removed
     * - Case is preserved
     *
     * EXAMPLES:
     * - 'App\\Services\\UserService' â†’ 'App.Services.UserService'
     * - 'App/Services/UserService.php' â†’ 'App.Services.UserService.php'
     * - '\\GlobalClass' â†’ 'GlobalClass'
     *
     * @param string $className The fully qualified class name to convert
     *
     * @return string The converted service identifier
     */
    public static function classToId(string $className): string
    {
        return ltrim(str_replace(['\\', '/'], '.', $className), '.');
    }

    /**
     * Converts a container service identifier back to a potential class name.
     *
     * Reverses the classToId transformation by converting dots back to namespace
     * separators. Note that this is a lossy operation as directory separators
     * cannot be distinguished from namespace separators in the result.
     *
     * CONVERSION RULES:
     * - Dots (.) become namespace separators (\)
     * - No validation of resulting class name validity
     * - Case is preserved
     *
     * EXAMPLES:
     * - 'App.Services.UserService' â†’ 'App\\Services\\UserService'
     * - 'GlobalClass' â†’ 'GlobalClass'
     *
     * @param string $serviceId The service identifier to convert
     *
     * @return string The potential class name
     */
    public static function idToClass(string $serviceId): string
    {
        return str_replace('.', '\\', $serviceId);
    }

    /**
     * Generates a filesystem-safe cache key from an arbitrary input string.
     *
     * Sanitizes the input string to contain only characters safe for use in
     * filenames and cache keys. All unsafe characters are replaced with
     * underscores, ensuring compatibility with various caching backends
     * and filesystems.
     *
     * SANITIZATION RULES:
     * - Allowed characters: letters, digits, underscore, dot, hyphen
     * - All other characters become underscores
     * - Multiple consecutive underscores are preserved
     * - Case is preserved
     *
     * EXAMPLES:
     * - 'App\\Services\\UserService' â†’ 'App_Services_UserService'
     * - 'complex/key:with@symbols' â†’ 'complex_key_with_symbols'
     * - 'normal-string' â†’ 'normal-string'
     *
     * @param string $input The input string to sanitize
     *
     * @return string The sanitized cache key
     */
    public static function toCacheKey(string $input): string
    {
        return preg_replace('/[^a-zA-Z0-9_.-]/', '_', $input);
    }

    /**
     * Extracts the namespace portion from a fully qualified class name.
     *
     * Parses the class name and returns everything before the last namespace
     * separator, effectively giving the containing namespace. Returns empty
     * string for classes in the global namespace.
     *
     * EXTRACTION RULES:
     * - Splits on namespace separators
     * - Removes the last segment (class name)
     * - Rejoins remaining segments
     * - Empty string for global classes
     *
     * EXAMPLES:
     * - 'App\\Services\\UserService' â†’ 'App\\Services'
     * - 'GlobalClass' â†’ ''
     * - 'Vendor\\Package\\Sub\\Class' â†’ 'Vendor\\Package\\Sub'
     *
     * @param string $className The fully qualified class name
     *
     * @return string The namespace, or empty string for global classes
     */
    public static function extractNamespace(string $className): string
    {
        $parts = explode('\\', $className);
        array_pop($parts); // Remove class name

        return implode('\\', $parts);
    }

    /**
     * Extracts the short class name from a fully qualified class name.
     *
     * Returns only the final segment after the last namespace separator,
     * providing the actual class identifier without namespace qualification.
     *
     * EXTRACTION RULES:
     * - Splits on namespace separators
     * - Returns the last segment
     * - Works with any number of namespace levels
     *
     * EXAMPLES:
     * - 'App\\Services\\UserService' â†’ 'UserService'
     * - 'GlobalClass' â†’ 'GlobalClass'
     * - 'Vendor\\Package\\Sub\\DeepClass' â†’ 'DeepClass'
     *
     * @param string $className The fully qualified class name
     *
     * @return string The short class name
     */
    public static function extractClassName(string $className): string
    {
        $parts = explode('\\', $className);

        return end($parts);
    }

    /**
     * Checks if a string starts with any of the provided prefixes.
     *
     * Performs efficient prefix matching against multiple candidates,
     * returning true on the first successful match. Useful for namespace
     * whitelisting, security checks, and configuration filtering.
     *
     * MATCHING BEHAVIOR:
     * - Case-sensitive comparison
     * - Short-circuit on first match
     * - Empty prefix array returns false
     * - Empty haystack matches only empty prefix
     *
     * PERFORMANCE NOTES:
     * - O(n*m) where n is prefix count, m is string length
     * - Optimized for small prefix arrays
     * - Consider pre-sorting prefixes for very large sets
     *
     * @param string $haystack The string to check
     * @param array  $prefixes Array of prefix strings to match against
     *
     * @return bool True if any prefix matches, false otherwise
     */
    public static function startsWithAny(string $haystack, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($haystack, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a string ends with any of the provided suffixes.
     *
     * Performs efficient suffix matching against multiple candidates,
     * returning true on the first successful match. Useful for file extension
     * checking, class type identification, and pattern matching.
     *
     * MATCHING BEHAVIOR:
     * - Case-sensitive comparison
     * - Short-circuit on first match
     * - Empty suffix array returns false
     * - Empty haystack matches only empty suffix
     *
     * PERFORMANCE NOTES:
     * - O(n*m) where n is suffix count, m is string length
     * - Optimized for small suffix arrays
     * - Consider pre-sorting suffixes for very large sets
     *
     * @param string $haystack The string to check
     * @param array  $suffixes Array of suffix strings to match against
     *
     * @return bool True if any suffix matches, false otherwise
     */
    public static function endsWithAny(string $haystack, array $suffixes): bool
    {
        foreach ($suffixes as $suffix) {
            if (str_ends_with($haystack, $suffix)) {
                return true;
            }
        }

        return false;
    }
}
