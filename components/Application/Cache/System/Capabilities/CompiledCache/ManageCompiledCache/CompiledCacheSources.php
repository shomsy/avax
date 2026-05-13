<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

final class CompiledCacheSources
{
    /** @var array<string, CompiledCacheSource> */
    private array $sources = [];

    public function __construct(CompiledCacheSource ...$compiledCacheSource)
    {
        foreach ($compiledCacheSource as $source) {
            $this->sources[$source->path] = $source;
        }
    }

    public static function empty(): self
    {
        return new self();
    }

    public static function fromPaths(Filesystem $filesystem, string ...$paths) : self
    {
        $sources = array_map(
            static fn (string $path) : CompiledCacheSource => CompiledCacheSource::fromPath(path: $path, filesystem: $filesystem),
            $paths,
        );

        return new self(...$sources);
    }

    public function add(CompiledCacheSource $compiledCacheSource): self
    {
        $new = clone $this;
        $new->sources[$compiledCacheSource->path] = $compiledCacheSource;

        return $new;
    }

    public function has(string $path): bool
    {
        return isset($this->sources[$path]);
    }

    public function get(string $path) : CompiledCacheSource|null
    {
        return $this->sources[$path] ?? null;
    }

    public function count(): int
    {
        return count($this->sources);
    }

    public function fingerprint(): string
    {
        $fingerprints = [];

        foreach ($this->sources as $path => $source) {
            $fingerprints[$path] = $source->fingerprint();
        }

        ksort($fingerprints);

        return md5(implode('|', $fingerprints));
    }

    /**
     * @return array<string, CompiledCacheSource>
     */
    public function all(): array
    {
        return $this->sources;
    }
}
