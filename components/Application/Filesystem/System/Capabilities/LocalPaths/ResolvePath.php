<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\LocalPaths;

final readonly class ResolvePath
{
    public function __construct(
        private NormalizePath $normalizePath = new NormalizePath(),
    ) {}

    public function execute(string $path, string $root = '') : string
    {
        $normalized = $this->normalizePath->execute($path);

        if ($root !== '') {
            $root       = rtrim($this->normalizePath->execute($root), '/');
            $normalized = $root . '/' . ltrim($normalized, '/');
        }

        $resolved = realpath($normalized);

        return $resolved !== false ? $resolved : $normalized;
    }
}