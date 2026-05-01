<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\PublicSurface;

use Avax\Components\Application\Text\System\Capabilities\CaseConversion\Str;
use Avax\Components\Application\Text\System\Capabilities\Transform\ToPlural;
use Avax\Components\Application\Text\System\Capabilities\Transform\ToSingular;
use Avax\Components\Application\Text\System\Capabilities\Validate\IsValidEmail;
use Closure;
use Override;
use Stringable;

/**
 * Text Public Surface.
 *
 * Provides a fluent DSL for string manipulation, delegating complex
 * linguistic and validation logic to internal System Capabilities.
 */
final readonly class Text implements Stringable
{
    private function __construct(public string $value) {}

    public static function of(string $value) : self
    {
        return new self($value);
    }

    public static function fromNullable(?string $value, string $default = '') : self
    {
        return new self($value ?? $default);
    }

    #[Override]
    public function __toString() : string
    {
        return $this->value;
    }

    // ──────────────────────────────────────────────
    // Transformations (Delegated to Capabilities)
    // ──────────────────────────────────────────────

    public function plural(int $count = 2) : self
    {
        return new self((new ToPlural)->execute($this->value, $count));
    }

    public function singular() : self
    {
        return new self((new ToSingular)->execute($this->value));
    }

    public function lower() : self
    {
        return new self(mb_strtolower($this->value, 'UTF-8'));
    }

    public function upper() : self
    {
        return new self(mb_strtoupper($this->value, 'UTF-8'));
    }

    public function slug(string $separator = '-') : self
    {
        return new self(Str::slug($this->value, $separator));
    }

    public function camel() : self
    {
        return new self(Str::camel($this->value));
    }

    public function snake(string $delimiter = '_') : self
    {
        return new self(Str::snake($this->value, $delimiter));
    }

    public function kebab() : self
    {
        return new self(Str::kebab($this->value));
    }

    public function studly() : self
    {
        return new self(Str::studly($this->value));
    }

    public function headline() : self
    {
        return new self(Str::headline($this->value));
    }

    public function limit(int $max, string $suffix = '…') : self
    {
        return new self(Str::limit($this->value, $max, $suffix));
    }

    // ──────────────────────────────────────────────
    // Validation (Delegated to Capabilities)
    // ──────────────────────────────────────────────

    public function isValidEmail() : bool
    {
        return (new IsValidEmail)->execute($this->value);
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

    public function length() : int
    {
        return mb_strlen($this->value, 'UTF-8');
    }

    public function before(string $needle) : self
    {
        $pos = strpos($this->value, $needle);
        if ($pos === false) {
            return $this;
        }

        return new self(substr($this->value, 0, $pos));
    }

    public function after(string $needle) : self
    {
        $pos = strpos($this->value, $needle);
        if ($pos === false) {
            return $this;
        }

        return new self(substr($this->value, $pos + strlen($needle)));
    }

    public function between(string $left, string $right) : self
    {
        $leftPos = strpos($this->value, $left);
        if ($leftPos === false) {
            return new self('');
        }

        $rightPos = strpos($this->value, $right, $leftPos + strlen($left));
        if ($rightPos === false) {
            return new self('');
        }

        return new self(substr($this->value, $leftPos + strlen($left), $rightPos - $leftPos - strlen($left)));
    }

    public function ensurePrefix(string $prefix) : self
    {
        return str_starts_with($this->value, $prefix)
            ? $this
            : new self($prefix . $this->value);
    }

    public function ensureSuffix(string $suffix) : self
    {
        return str_ends_with($this->value, $suffix)
            ? $this
            : new self($this->value . $suffix);
    }

    public function excerpt(int $length = 200, string $suffix = '...') : self
    {
        return new self(Str::excerpt($this->value, $length, $suffix));
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

        $v = preg_replace('/[^\x20-\x7E]/', '', $v);

        return new self(is_string($v) ? $v : $this->value);
    }

    public function toString() : string
    {
        return $this->value;
    }
}
