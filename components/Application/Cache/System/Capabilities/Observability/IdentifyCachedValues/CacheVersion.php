<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues;

use InvalidArgumentException;
use Override;
use Stringable;

final readonly class CacheVersion implements Stringable
{
    public function __construct(
        public int $major,
        public int $minor = 0,
        public int $patch = 0,
    )
    {
        if ($major < 0 || $minor < 0 || $patch < 0) {
            throw new InvalidArgumentException(message: 'Version numbers must be non-negative');
        }
    }

    public static function create(int $major, int $minor = 0, int $patch = 0) : self
    {
        return new self(major: $major, minor: $minor, patch: $patch);
    }

    public static function fromString(string $version) : self
    {
        $parts = explode('.', $version);
        $major = (int) ($parts[0] ?? '0');
        $minor = (int) ($parts[1] ?? '0');
        $patch = (int) ($parts[2] ?? '0');

        return new self(major: $major, minor: $minor, patch: $patch);
    }

    public static function current() : self
    {
        return new self(major: 1);
    }

    public function major() : int
    {
        return $this->major;
    }

    public function minor() : int
    {
        return $this->minor;
    }

    public function patch() : int
    {
        return $this->patch;
    }

    public function isCompatibleWith(self $other) : bool
    {
        return $this->major === $other->major;
    }

    public function incrementMajor() : self
    {
        return new self(major: $this->major + 1, minor: 0, patch: 0);
    }

    public function incrementMinor() : self
    {
        return new self(major: $this->major, minor: $this->minor + 1, patch: 0);
    }

    public function incrementPatch() : self
    {
        return new self(major: $this->major, minor: $this->minor, patch: $this->patch + 1);
    }

    #[Override]
    public function __toString() : string
    {
        return $this->toString();
    }

    public function toString() : string
    {
        return sprintf('%d.%d.%d', $this->major, $this->minor, $this->patch);
    }
}
