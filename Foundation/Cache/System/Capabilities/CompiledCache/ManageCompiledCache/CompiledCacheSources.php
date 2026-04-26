<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

final class CompiledCacheSources
{
    /** @var array<string, CompiledCacheSource> */
    private array $sources = [];

    public function __construct(CompiledCacheSource ...$sources)
    {
        foreach ($sources as $source) {
            $this->sources[$source->path] = $source;
        }
    }

    public static function empty() : self
    {
        return new self();
    }

    public static function fromPaths(string ...$paths) : self
    {
        $sources = array_map(
            fn (string $path) => CompiledCacheSource::fromPath(path: $path),
            $paths
        );

        return new self(...$sources);
    }

    public function add(CompiledCacheSource $source) : self
    {
        $new                         = clone $this;
        $new->sources[$source->path] = $source;

        return $new;
    }

    public function has(string $path) : bool
    {
        return isset($this->sources[$path]);
    }

    public function get(string $path) : CompiledCacheSource|null
    {
        return $this->sources[$path] ?? null;
    }

    public function count() : int
    {
        return count($this->sources);
    }

    public function fingerprint() : string
    {
        $fingerprints = [];

        foreach ($this->sources as $path => $source) {
            $fingerprints[$path] = $source->fingerprint();
        }

        ksort($fingerprints);

        return md5(implode('|', $fingerprints));
    }

    public function all() : array
    {
        return $this->sources;
    }
}