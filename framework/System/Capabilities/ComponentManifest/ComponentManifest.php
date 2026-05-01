<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ComponentManifest;

use Avax\Framework\System\Foundation\Version\Version;

final readonly class ComponentManifest
{
    /**
     * @param list<string> $dependencies
     */
    public function __construct(
        public string $name,
        public string $version,
        public ?string $path = null,
        public array   $dependencies = [],
    ) {}

    public function version() : Version
    {
        return Version::from($this->version);
    }

    public function isCompatibleWith(self $other) : bool
    {
        return $this->version()->isCompatibleWith(other: $other->version());
    }
}
