<?php

declare(strict_types=1);
namespace Avax\Container\Features\Core\Utils;

/**
 * Array utility functions for container operations.
 *
 * Provides safe array operations used for configuration merging,
 * parameter processing, and data transformation throughout the container.
 */
final class ArrayTools
{
    /**
     * Recursively merge arrays with proper handling of numeric keys.
     */
    public static function mergeRecursive(array ...$arrays) : array
    {
        $result = array_shift($arrays);

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                    $result[$key] = self::mergeRecursive($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Check if array is associative (has string keys).
     */
    public static function isAssociative(array $array) : bool
    {
        if (empty($array)) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Get value from nested array using dot notation.
     */
    public static function getNested(array $array, string $key, mixed $default = null) : mixed
    {
        $keys = explode('.', $key);

        foreach ($keys as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Set value in nested array using dot notation.
     */
    public static function setNested(array &$array, string $key, mixed $value) : void
    {
        $keys    = explode('.', $key);
        $current = &$array;

        foreach ($keys as $segment) {
            if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                $current[$segment] = [];
            }
            $current = &$current[$segment];
        }

        $current = $value;
    }

    /**
     * Flatten nested array with dot notation keys.
     */
    public static function flatten(array $array, string $prefix = '') : array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $result += self::flatten(array: $value, prefix: $newKey);
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Filter array recursively, removing null/empty values.
     */
    public static function filterRecursive(array $array, bool $removeEmpty = true) : array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::filterRecursive(array: $value, removeEmpty: $removeEmpty);
                if ($removeEmpty && empty($array[$key])) {
                    unset($array[$key]);
                }
            } elseif ($value === null || ($removeEmpty && empty($value))) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Get array intersection by comparing serialized values.
     */
    public static function intersectByValue(array $array1, array $array2) : array
    {
        return array_intersect(
            array_map('serialize', $array1),
            array_map('serialize', $array2)
        );
    }
}