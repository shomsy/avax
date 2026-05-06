<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\CaseConversion;

use Override;

/**
 * Fluent string wrapper providing method chaining for string operations.
 */
final readonly class Stringable implements \Stringable
{
    public function __construct(private string $value = '')
    {
    }

    public static function of(string $value): self
    {
        return new self($value);
    }

    #[Override]
    public function __toString(): string
    {
        return $this->value;
    }

    // ─── Case Conversion ───

    public function slug(string $separator = '-'): self
    {
        return new self(Str::slug($this->value, $separator));
    }

    public function plural(int $count = 2): self
    {
        return new self(Str::plural($this->value, $count));
    }

    public function singular(): self
    {
        return new self(Str::singular($this->value));
    }

    public function camel(): self
    {
        return new self(Str::camel($this->value));
    }

    public function snake(string $delimiter = '_'): self
    {
        return new self(Str::snake($this->value, $delimiter));
    }

    public function kebab(): self
    {
        return new self(Str::kebab($this->value));
    }

    public function studly(): self
    {
        return new self(Str::studly($this->value));
    }

    public function headline(): self
    {
        return new self(Str::headline($this->value));
    }

    public function lower(): self
    {
        return new self(mb_strtolower($this->value, 'UTF-8'));
    }

    public function upper(): self
    {
        return new self(mb_strtoupper($this->value, 'UTF-8'));
    }

    public function ucfirst(): self
    {
        return new self(ucfirst($this->value));
    }

    public function lcfirst(): self
    {
        return new self(lcfirst($this->value));
    }

    // ─── Checks ───

    public function contains(string $needle): bool
    {
        return str_contains($this->value, $needle);
    }

    public function startsWith(string $prefix): bool
    {
        return str_starts_with($this->value, $prefix);
    }

    public function endsWith(string $suffix): bool
    {
        return str_ends_with($this->value, $suffix);
    }

    public function isEmpty(): bool
    {
        return $this->value === '';
    }

    public function isNotEmpty(): bool
    {
        return $this->value !== '';
    }

    // ─── Transformations ───

    public function limit(int $limit = 100, string $end = '...'): self
    {
        return new self(Str::limit($this->value, $limit, $end));
    }

    public function excerpt(int $length = 200, string $suffix = '...'): self
    {
        return new self(Str::excerpt($this->value, $length, $suffix));
    }

    public function replace(string $search, string $replace): self
    {
        return new self(str_replace($search, $replace, $this->value));
    }

    public function trim(string $characters = " \t\n\r\0\x0B"): self
    {
        return new self(trim($this->value, $characters));
    }

    public function ltrim(string $characters = " \t\n\r\0\x0B"): self
    {
        return new self(ltrim($this->value, $characters));
    }

    public function rtrim(string $characters = " \t\n\r\0\x0B"): self
    {
        return new self(rtrim($this->value, $characters));
    }

    public function substr(int $start, ?int $length = null): self
    {
        return new self(
            $length !== null
                ? mb_substr($this->value, $start, $length, 'UTF-8')
                : mb_substr($this->value, $start, null, 'UTF-8'),
        );
    }

    public function pad(int $length, string $padString = ' ', int $type = STR_PAD_RIGHT): self
    {
        return new self(str_pad($this->value, $length, $padString, $type));
    }

    public function repeat(int $times): self
    {
        return new self(str_repeat($this->value, $times));
    }

    public function reverse(): self
    {
        return new self(strrev($this->value));
    }

    // ─── Accessors ───

    public function length(): int
    {
        return mb_strlen($this->value, 'UTF-8');
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function value(): string
    {
        return $this->value;
    }

    // ─── Pipe ───

    public function pipe(callable $callback): self
    {
        return new self($callback($this->value));
    }
}
