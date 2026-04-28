<?php

declare(strict_types=1);

require_once __DIR__ . '/BenchmarkPeerAdapter.php';

/**
 * Loads one peer comparison target from an existing JSON artifact.
 */
final readonly class ArtifactBenchmarkPeerAdapter implements BenchmarkPeerAdapter
{
    private string $path;
    private string $peerName;

    public function __construct(
        string $peerName,
        string $path
    )
    {
        $this->peerName = $peerName;
        $this->path     = $path;
    }

    public function name() : string
    {
        return $this->peerName;
    }

    /**
     * @throws JsonException
     */
    public function load() : array
    {
        if (! is_file(filename: $this->path)) {
            throw new RuntimeException(message: "Benchmark artifact [{$this->path}] does not exist.");
        }

        $json = file_get_contents(filename: $this->path);
        if (! is_string(value: $json) || $json === '') {
            throw new RuntimeException(message: "Benchmark artifact [{$this->path}] could not be read.");
        }

        $decoded = json_decode(json: $json, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
        if (! is_array(value: $decoded) || ! is_array(value: $decoded['results'] ?? null)) {
            throw new RuntimeException(message: "Benchmark artifact [{$this->path}] is invalid.");
        }

        $meta = $decoded['meta'] ?? [];
        if (! is_array(value: $meta)) {
            $meta = [];
        }

        /** @var array<string, array<string, mixed>> $results */
        $results = $decoded['results'];

        return [
            'meta'    => $meta,
            'results' => $results,
        ];
    }
}
