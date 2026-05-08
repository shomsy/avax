<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataPaths;

/**
 * Handles dot notation path parsing and manipulation.
 */
final readonly class DotPath
{
    private const string DELIMITER = '.';

    public function __construct(
        private string $path,
    ) {
    }

    public function getParentPath(): string
    {
        $segments = $this->getSegments();
        array_pop(array: $segments);

        return implode(separator: self::DELIMITER, array: $segments);
    }

    public function getSegments(): array
    {
        return explode(separator: self::DELIMITER, string: $this->path);
    }

    public function getKey(): string
    {
        $segments = $this->getSegments();

        return end(array: $segments);
    }

    public function isNested(): bool
    {
        return count(value: $this->getSegments()) > 1;
    }

    public function getValue(array $items, mixed $default = null): mixed
    {
        $current = $items;

        foreach ($this->getSegments() as $segment) {
            if (! is_array(value: $current) || ! array_key_exists(key: $segment, array: $current)) {
                return $default;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    public function setValue(array &$items, mixed $value): void
    {
        $keys = $this->getSegments();
        $current = &$items;

        while (count(value: $keys) > 1) {
            $segment = array_shift(array: $keys);

            if (! isset($current[$segment]) || ! is_array(value: $current[$segment])) {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }

        $current[array_shift(array: $keys)] = $value;
    }

    public function unsetValue(array &$items): bool
    {
        $keys = $this->getSegments();
        $current = &$items;

        while (count(value: $keys) > 1) {
            $segment = array_shift(array: $keys);

            if (! isset($current[$segment]) || ! is_array(value: $current[$segment])) {
                return false;
            }

            $current = &$current[$segment];
        }

        unset($current[array_shift(array: $keys)]);

        return true;
    }

    public function exists(array $items): bool
    {
        $current = $items;

        foreach ($this->getSegments() as $segment) {
            if (! is_array(value: $current) || ! array_key_exists(key: $segment, array: $current)) {
                return false;
            }

            $current = $current[$segment];
        }

        return true;
    }
}
