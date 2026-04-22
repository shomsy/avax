<?php

declare(strict_types=1);

namespace Avax\Text;

/**
 * Immutable regex pattern DSL.
 *
 * Provides clean, chainable regex operations with proper error handling.
 */
final readonly class Pattern
{
    public string $raw;
    public string $flags;
    public string $delimiter;

    private function __construct(string $raw, string|null $flags = null, string $delimiter = '~')
    {
        $flags           ??= '';
        $this->raw       = $raw;
        $this->flags     = $flags;
        $this->delimiter = $delimiter;
    }

    public static function of(string $raw, string|null $flags = null, string $delimiter = '~') : self
    {
        $flags ??= '';

        return new self(raw: $raw, flags: $flags, delimiter: $delimiter);
    }

    /**
     * Test if pattern matches subject.
     */
    public function test(string $subject) : bool
    {
        $pattern = $this->toPreg();
        $result  = preg_match($pattern, $subject);

        if ($result === false) {
            throw RegexException::fromLastError(pattern: $pattern);
        }

        return $result === 1;
    }

    /**
     * Convert to preg-compatible pattern string.
     */
    public function toPreg() : string
    {
        $d       = $this->delimiter;
        $escaped = str_replace($d, '\\' . $d, $this->raw);

        return $d . $escaped . $d . $this->flags;
    }

    /**
     * Match pattern against subject and return result.
     */
    public function match(string $subject) : MatchResult
    {
        $pattern = $this->toPreg();
        $matches = [];
        $result  = preg_match($pattern, $subject, $matches);

        if ($result === false) {
            throw RegexException::fromLastError(pattern: $pattern);
        }

        return new MatchResult(matched: $result === 1, matches: $matches);
    }

    /**
     * Replace pattern in subject with replacement.
     */
    public function replace(string $subject, string $replacement) : string
    {
        $pattern = $this->toPreg();
        $result  = preg_replace($pattern, $replacement, $subject);

        if (! is_string($result)) {
            throw RegexException::fromLastError(pattern: $pattern);
        }

        return $result;
    }

    /**
     * Replace using callback function.
     */
    public function replaceCallback(string $subject, callable $fn) : string
    {
        $pattern = $this->toPreg();
        $result  = preg_replace_callback($pattern, $fn, $subject);

        if (! is_string($result)) {
            throw RegexException::fromLastError(pattern: $pattern);
        }

        return $result;
    }

    /**
     * Match all occurrences and return array of matches.
     */
    public function matchAll(string $subject) : array
    {
        $pattern = $this->toPreg();
        $matches = [];
        $result  = preg_match_all($pattern, $subject, $matches, PREG_SET_ORDER);

        if ($result === false) {
            throw RegexException::fromLastError(pattern: $pattern);
        }

        return $matches;
    }

    /**
     * Split subject by pattern.
     */
    public function split(string $subject, int|null $limit = null, int $flags = 0) : array
    {
        $limit   ??= -1;
        $pattern = $this->toPreg();
        $result  = preg_split($pattern, $subject, $limit, $flags);

        if (! is_array($result)) {
            throw RegexException::fromLastError(pattern: $pattern);
        }

        return $result;
    }
}