<?php

declare(strict_types=1);

namespace Avax\Text;

/**
 * Immutable result of regex match operation.
 */
final readonly class MatchResult
{
    /**
     * @param array<int|string, string> $matches
     */
    public function __construct(
        public bool  $matched,
        public array $matches
    ) {}

    /**
     * Get named group value or null if not found.
     */
    public function group(string $name) : string|null
    {
        return $this->matches[$name] ?? null;
    }

    /**
     * Get all named groups as associative array.
     */
    public function namedGroups() : array
    {
        return array_filter($this->matches, static function ($key) { return ! is_int($key); }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get full match (index 0).
     */
    public function fullMatch() : string|null
    {
        return $this->matches[0] ?? null;
    }
}