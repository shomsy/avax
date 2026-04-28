<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use RuntimeException;

final class CompiledCacheManifest
{
    /** @var array<string, CompiledCacheManifestEntry> */
    private array $entries = [];

    public function __construct(
        private CompiledCacheManifestEntry|null $entry = null
    )
    {
        if ($entry !== null) {
            $this->entries[$entry->name->toString()] = $entry;
        }
    }

    public static function load(string $path) : self
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
            $entry = CompiledCacheManifestEntry::fromArray(data: $entryData);
            $manifest->set(entry: $entry);
        }

        return $manifest;
    }

    public function set(CompiledCacheManifestEntry $entry) : void
    {
        $this->entries[$entry->name->toString()] = $entry;
    }

    public static function empty() : self
    {
        return new self();
    }

    public function has(string $name) : bool
    {
        return isset($this->entries[$name]);
    }

    public function remove(string $name) : void
    {
        unset($this->entries[$name]);
    }

    public function isFresh(string $name, CompiledCacheSources $sources) : bool
    {
        $entry = $this->get(name: $name);

        if ($entry === null) {
            return false;
        }

        return $entry->sourceFingerprint === $sources->fingerprint();
    }

    public function get(string $name) : CompiledCacheManifestEntry|null
    {
        return $this->entries[$name] ?? null;
    }

    public function all() : array
    {
        return $this->entries;
    }

    public function count() : int
    {
        return count($this->entries);
    }

    public function save(string $path) : void
    {
        $entries = array_map(
            fn (CompiledCacheManifestEntry $entry) => $entry->toArray(),
            $this->entries
        );

        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($entries, true) . ";\n";

        file_put_contents($path, $content, LOCK_EX);
    }
}