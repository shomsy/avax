<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Version;

use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;

/**
 * Represents a concrete semantic version string.
 */
final readonly class Version
{
    private function __construct(private string $value)
    {
        if (preg_match('/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $this->value) !== 1) {
            throw new FrameworkMisconfigured(
                message: sprintf('Version "%s" is not a valid semantic version.', $this->value),
            );
        }
    }

    public static function from(string $value): self
    {
        return new self(value: trim(string: $value));
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function isCompatibleWith(self $other): bool
    {
        return version_compare($this->value, $other->value, '>=');
    }
}
