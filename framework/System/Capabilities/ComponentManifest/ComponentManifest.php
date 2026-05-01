<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ComponentManifest;

use Avax\Framework\System\Foundation\Version;

final readonly class ComponentManifest
{
    public function __construct(
        public string      $name,
        public string      $version,
        public string|null $path = null,
        public array       $dependencies = [],
    ) {}

    public function version() : Version
    {
        return Version::from($this->version);
    }

    public function isCompatibleWith(self $other) : bool
    {
        return $this->version === $other->version;
    }
}