<?php

declare(strict_types=1);

namespace Avax\Components\API\Contracts\System\Capabilities\Versioning;

final readonly class ApiVersion
{
    public function __construct(
        public string $major = '1',
        public string $minor = '0',
        public string $patch = '0',
    ) {}

    public static function fromString(string $version) : self
    {
        $parts = $version === '' ? [] : explode(separator: '.', string: $version);

        return new self(
            major: $parts[0] ?? '1',
            minor: $parts[1] ?? '0',
            patch: $parts[2] ?? '0',
        );
    }

    public function toString() : string
    {
        return "{$this->major}.{$this->minor}.{$this->patch}";
    }

    public function isMajorChange(self $other) : bool
    {
        return $this->major !== $other->major;
    }

    public function isMinorChange(self $other) : bool
    {
        return $this->major === $other->major && $this->minor !== $other->minor;
    }

    public function isCompatible(self $other) : bool
    {
        return $this->major === $other->major;
    }
}
