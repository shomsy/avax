<?php

declare(strict_types=1);

namespace Avax\Text;

use Closure;

/**
 * Immutable text processing DSL.
 *
 * Provides fluent, chainable string operations with functional programming support.
 * All operations return new Text instances, maintaining immutability.
 */
final readonly class Text
{
    public string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromNullable(string|null $value, string $default = '') : self
    {
        return new self(value: $value ?? $default);
    }

    public function __toString() : string
    {
        return $this->value;
    }

    /**
     * Map is alias for pipe.
     */
    public function map(Closure $fn) : self
    {
        return $this->pipe(fn: $fn);
    }

    /**
     * Functional pipe: apply transformation function.
     */
    public function pipe(Closure $fn) : self
    {
        return new self(value: $fn($this->value));
    }

    /**
     * Tap for side effects without changing value.
     */
    public function tap(Closure $fn) : self
    {
        $fn($this->value);

        return $this;
    }

    /**
     * Conditional transformation.
     */
    public function when(bool $condition, Closure $then, Closure|null $else = null) : self
    {
        if ($condition) {
            return $then($this);
        }

        return $else ? $else($this) : $this;
    }

    public function isEmpty() : bool
    {
        return $this->value === '';
    }

    public function isBlank() : bool
    {
        return trim($this->value) === '';
    }

    public function replaceRegexCallback(string $pattern, callable $fn, string $flags = '') : self
    {
        return new self(value: Pattern::of(raw: $pattern, flags: $flags)->replaceCallback(subject: $this->value, fn: $fn));
    }

    public static function of(string $value) : self
    {
        return new self(value: $value);
    }

    public function matchRegex(string $pattern, string $flags = '') : MatchResult
    {
        return Pattern::of(raw: $pattern, flags: $flags)->match(subject: $this->value);
    }

    public function testRegex(string $pattern, string $flags = '') : bool
    {
        return Pattern::of(raw: $pattern, flags: $flags)->test(subject: $this->value);
    }

    public function splitRegex(string $pattern, string $flags = '') : array
    {
        return Pattern::of(raw: $pattern, flags: $flags)->split(subject: $this->value);
    }

    public function before(string $needle, bool $last = false) : self
    {
        $pos = $last ? strrpos($this->value, $needle) : strpos($this->value, $needle);
        if ($pos === false) {
            return $this;
        }

        return new self(value: substr($this->value, 0, (int) $pos));
    }

    public function after(string $needle, bool $last = false) : self
    {
        $pos = $last ? strrpos($this->value, $needle) : strpos($this->value, $needle);
        if ($pos === false) {
            return $this;
        }

        return new self(value: substr($this->value, (int) $pos + strlen($needle)));
    }

    public function between(string $left, string $right) : self
    {
        $start = strpos($this->value, $left);
        if ($start === false) {
            return new self(value: '');
        }

        $start += strlen($left);
        $end   = strpos($this->value, $right, $start);
        if ($end === false) {
            return new self(value: '');
        }

        return new self(value: substr($this->value, $start, $end - $start));
    }

    public function contains(string $needle) : bool
    {
        return strpos($this->value, $needle) !== false;
    }

    public function ensurePrefix(string $prefix) : self
    {
        return $this->startsWith(prefix: $prefix) ? $this : new self(value: $prefix . $this->value);
    }

    public function startsWith(string $prefix) : bool
    {
        return strncmp($this->value, $prefix, strlen($prefix)) === 0;
    }

    public function ensureSuffix(string $suffix) : self
    {
        return $this->endsWith(suffix: $suffix) ? $this : new self(value: $this->value . $suffix);
    }

    public function endsWith(string $suffix) : bool
    {
        $len = strlen($suffix);
        if ($len === 0) {
            return true;
        }

        return substr($this->value, -$len) === $suffix;
    }

    public function stripPrefix(string $prefix) : self
    {
        return $this->startsWith(prefix: $prefix)
            ? new self(value: substr($this->value, strlen($prefix)))
            : $this;
    }

    public function stripSuffix(string $suffix) : self
    {
        return $this->endsWith(suffix: $suffix)
            ? new self(value: substr($this->value, 0, -strlen($suffix)))
            : $this;
    }

    public function limit(int $max, string $suffix = '…') : self
    {
        if ($max <= 0) {
            return new self(value: '');
        }

        if ($this->length() <= $max) {
            return $this;
        }

        if (function_exists('mb_substr')) {
            $cut = (string) mb_substr($this->value, 0, $max, 'UTF-8');

            return new self(value: $cut . $suffix);
        }

        return new self(value: substr($this->value, 0, $max) . $suffix);
    }

    public function length() : int
    {
        if (function_exists('mb_strlen')) {
            return (int) mb_strlen($this->value, 'UTF-8');
        }

        return strlen($this->value);
    }

    public function toInt(int|null $default = null) : int|null
    {
        $v = $this->trim()->toString();
        if ($v === '' || ! preg_match('~^[+-]?\d+$~', $v)) {
            return $default;
        }

        return (int) $v;
    }

    public function toString() : string
    {
        return $this->value;
    }

    public function trim(string $chars = " \t\n\r\0\x0B") : self
    {
        return new self(value: trim($this->value, $chars));
    }

    public function toFloat(float|null $default = null) : float|null
    {
        $v = $this->trim()->toString();
        if ($v === '' || ! is_numeric($v)) {
            return $default;
        }

        return (float) $v;
    }

    public function toBool(bool|null $default = null) : bool|null
    {
        $v = $this->trim()->lower()->toString();

        return match ($v) {
            '1', 'true', 'yes', 'y', 'on'  => true,
            '0', 'false', 'no', 'n', 'off' => false,
            default                        => $default,
        };
    }

    public function lower() : self
    {
        if (function_exists('mb_strtolower')) {
            return new self(value: (string) mb_strtolower($this->value, 'UTF-8'));
        }

        return new self(value: strtolower($this->value));
    }

    public function slug(string $separator = '-') : self
    {
        $s = $this->toAscii()->lower()->toString();
        $s = preg_replace('~[^a-z0-9]+~', $separator, $s);
        $s = trim((string) $s, $separator);

        return new self(value: (string) $s);
    }

    public function toAscii() : self
    {
        $v = $this->value;

        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $v);
            if (is_string($converted)) {
                $v = $converted;
            }
        }

        // Clean up non-ASCII characters
        $v = preg_replace('~[^\x20-\x7E]+~', '', $v);
        if (! is_string($v)) {
            $v = $this->value;
        }

        return new self(value: $v);
    }

    public function camel() : self
    {
        $studly = $this->studly()->toString();
        if ($studly === '') {
            return new self(value: '');
        }

        return new self(value: lcfirst($studly));
    }

    public function studly() : self
    {
        $s = $this->toAscii()->replaceRegex(pattern: '~[^a-zA-Z0-9]+~', replacement: ' ')->collapseWhitespace()->toString();

        $parts = explode(' ', $s);
        $parts = array_map(
            static fn(string $p) : string => $p === '' ? '' : ucfirst(strtolower($p)),
            $parts
        );

        return new self(value: implode('', $parts));
    }

    public function collapseWhitespace() : self
    {
        return $this->replaceRegex(pattern: '~\s+~u', replacement: ' ')->trim();
    }

    public function replaceRegex(string $pattern, string $replacement, string $flags = '') : self
    {
        return new self(value: Pattern::of(raw: $pattern, flags: $flags)->replace(subject: $this->value, replacement: $replacement));
    }

    public function replace(string $search, string $replace) : self
    {
        return new self(value: str_replace($search, $replace, $this->value));
    }

    public function snake(string $delimiter = '_') : self
    {
        $s = $this->value;
        $s = preg_replace('~([a-z0-9])([A-Z])~', '$1' . $delimiter . '$2', $s);
        $s = preg_replace('~[\s\-]+~', $delimiter, (string) $s);

        return Text::of(value: (string) $s)->lower();
    }

    // DSL Methods - Human-readable operations without exposing regex patterns

    public function containsOnlyDigits() : bool
    {
        return Pattern::of(raw: '^\d+$')->test(subject: $this->value);
    }

    public function containsOnlyLetters() : bool
    {
        return Pattern::of(raw: '^[a-zA-Z]+$')->test(subject: $this->value);
    }

    public function containsOnlyAlphanumeric() : bool
    {
        return Pattern::of(raw: '^[a-zA-Z0-9]+$')->test(subject: $this->value);
    }

    public function isValidEmail() : bool
    {
        return Pattern::of(raw: '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$')->test(subject: $this->value);
    }

    public function isValidUrl() : bool
    {
        return Pattern::of(raw: '^https?://[^\s/$.?#].[^\s]*$')->test(subject: $this->value);
    }

    public function isValidSlug() : bool
    {
        return Pattern::of(raw: '^[a-z0-9]+(?:-[a-z0-9]+)*$')->test(subject: $this->value);
    }

    public function extractDigits() : self
    {
        return new self(value: Pattern::of(raw: '\D+')->replace(subject: $this->value, replacement: ''));
    }

    public function extractLetters() : self
    {
        return new self(value: Pattern::of(raw: '[^a-zA-Z]')->replace(subject: $this->value, replacement: ''));
    }

    public function countWords() : int
    {
        return count($this->extractWords());
    }

    public function extractWords() : array
    {
        $matches = Pattern::of(raw: '\b\w+\b')->matchAll(subject: $this->value);
        $words   = [];
        foreach ($matches as $match) {
            $words[] = $match[0] ?? '';
        }

        return array_filter($words);
    }

    public function startsWithPattern(string $pattern) : bool
    {
        return Pattern::of(raw: '^' . preg_quote($pattern, '~'))->test(subject: $this->value);
    }

    public function endsWithPattern(string $pattern) : bool
    {
        return Pattern::of(raw: preg_quote($pattern, '~') . '$')->test(subject: $this->value);
    }

    public function containsPattern(string $pattern) : bool
    {
        return Pattern::of(raw: preg_quote($pattern, '~'))->test(subject: $this->value);
    }
}