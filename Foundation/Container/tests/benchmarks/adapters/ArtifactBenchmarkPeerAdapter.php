<?php

declare(strict_types=1);

require_once __DIR__ . '/BenchmarkPeerAdapter.php';

/**
 * Loads one peer comparison target from an existing JSON artifact.
 */
final readonly class ArtifactBenchmarkPeerAdapter implements BenchmarkPeerAdapter
{
    public function __construct(
        private string $peerName,
        private string $path
    ) {}

    public function name() : string
    {
        return $this->peerName;
    }

    public function load() : array
    {
        if (! is_file($this->path)) {
            throw new RuntimeException("Benchmark artifact [{$this->path}] does not exist.");
        }

        $json = file_get_contents($this->path);
        if (! is_string($json) || $json === '') {
            throw new RuntimeException("Benchmark artifact [{$this->path}] could not be read.");
        }

        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($decoded) || ! is_array($decoded['results'] ?? null)) {
            throw new RuntimeException("Benchmark artifact [{$this->path}] is invalid.");
        }

        $meta = $decoded['meta'] ?? [];
        if (! is_array($meta)) {
            $meta = [];
        }

        /** @var array<string, array<string, mixed>> $results */
        $results = $decoded['results'];

        return [
            'meta' => $meta,
            'results' => $results,
        ];
    }
}
