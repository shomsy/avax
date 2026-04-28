<?php

declare(strict_types=1);

namespace Avax\Components\Text\System\PublicSurface;

use Avax\Components\Text\System\Capabilities\Transform\ToPlural;
use Avax\Components\Text\System\Capabilities\Transform\ToSingular;
use Avax\Components\Text\System\Capabilities\Validate\IsValidEmail;
use Avax\Components\Text\System\Foundation\MatchResult;
use Avax\Components\Text\System\Foundation\Pattern;
use Closure;

/**
 * Text Public Surface.
 *
 * Provides a fluent DSL for string manipulation, delegating complex
 * linguistic and validation logic to internal System Capabilities.
 */
final readonly class Text
{
    private function __construct(public string $value) {}

    public static function of(string $value) : self
    {
        return new self($value);
    }

    public function __toString() : string
    {
        return $this->value;
    }

    // ──────────────────────────────────────────────
    // Transformations (Delegated to Capabilities)
    // ──────────────────────────────────────────────

    public function plural() : self
    {
        return new self((new ToPlural())->execute($this->value));
    }

    public function singular() : self
    {
        return new self((new ToSingular())->execute($this->value));
    }

    public function lower() : self
    {
        return new self(mb_strtolower($this->value, 'UTF-8'));
    }

    public function upper() : self
    {
        return new self(mb_strtoupper($this->value, 'UTF-8'));
    }

    // ──────────────────────────────────────────────
    // Validation (Delegated to Capabilities)
    // ──────────────────────────────────────────────

    public function isValidEmail() : bool
    {
        return (new IsValidEmail())->execute($this->value);
    }

    // ──────────────────────────────────────────────
    // Core DSL
    // ──────────────────────────────────────────────

    public function pipe(Closure $fn) : self
    {
        return new self($fn($this->value));
    }

    public function contains(string $needle) : bool
    {
        return str_contains($this->value, $needle);
    }

    public function startsWith(string $prefix) : bool
    {
        return str_starts_with($this->value, $prefix);
    }

    public function endsWith(string $suffix) : bool
    {
        return str_ends_with($this->value, $suffix);
    }

    public function trim(string $chars = " \t\n\r\0\x0B") : self
    {
        return new self(trim($this->value, $chars));
    }

    public function toString() : string
    {
        return $this->value;
    }
}