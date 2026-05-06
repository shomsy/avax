<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use RuntimeException;

final class CompiledCacheManifest
{
    /** @var array<string, CompiledCacheManifestEntry> */
    private array $entries = [];

    public function __construct(
        ?CompiledCacheManifestEntry $compiledCacheManifestEntry = null,
    ) {
        if ($compiledCacheManifestEntry instanceof CompiledCacheManifestEntry) {
            $this->entries[$compiledCacheManifestEntry->compiledCacheName->toString()] = $compiledCacheManifestEntry;
        }
    }

    public static function load(string $path): self
    {
        if (! file_exists($path)) {
            return new self();
        }

        $data = require $path;

        if (! is_array($data)) {
            throw new RuntimeException(message: 'Invalid manifest file');
        }

        $manifest = new self();

        foreach ($data as $entryData) {
            if (! is_array($entryData)) {
                throw new RuntimeException(message: 'Invalid manifest entry');
            }

            /** @var array{name: string, path: string, createdAt: int, sourceFingerprint: string} $entryData */
            $entry = CompiledCacheManifestEntry::fromArray(data: $entryData);
            $manifest->set(compiledCacheManifestEntry: $entry);
        }

        return $manifest;
    }

    public function set(CompiledCacheManifestEntry $compiledCacheManifestEntry): void
    {
        $this->entries[$compiledCacheManifestEntry->compiledCacheName->toString()] = $compiledCacheManifestEntry;
    }

    public static function empty(): self
    {
        return new self();
    }

    public function has(string $name): bool
    {
        return isset($this->entries[$name]);
    }

    public function remove(string $name): void
    {
        unset($this->entries[$name]);
    }

    public function isFresh(string $name, CompiledCacheSources $compiledCacheSources): bool
    {
        $entry = $this->get(name: $name);

        if (! $entry instanceof CompiledCacheManifestEntry) {
            return false;
        }

        return $entry->sourceFingerprint === $compiledCacheSources->fingerprint();
    }

    public function get(string $name): ?CompiledCacheManifestEntry
    {
        return $this->entries[$name] ?? null;
    }

    /**
     * @return array<string, CompiledCacheManifestEntry>
     */
    public function all(): array
    {
        return $this->entries;
    }

    public function count(): int
    {
        return count($this->entries);
    }

    public function save(string $path): void
    {
        $entries = array_map(
            static fn (CompiledCacheManifestEntry $compiledCacheManifestEntry): array => $compiledCacheManifestEntry->toArray(),
            $this->entries,
        );

        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($entries, true) . ";\n";

        file_put_contents($path, $content, LOCK_EX);
    }
}
