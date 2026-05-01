<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Configuration\CompiledCacheConfiguration;

final readonly class CompiledCacheConfiguration
{
    public function __construct(
        public string $directory,
        public bool $enabled = true,
        public bool $validateFreshness = true,
        public bool $failOnBuildError = true,
        public bool $useManifest = true,
    ) {
    }

    public static function inDirectory(string $directory): self
    {
        return new self(directory: $directory);
    }

    public static function disabled(): self
    {
        return new self(directory: '', enabled: false);
    }
}
