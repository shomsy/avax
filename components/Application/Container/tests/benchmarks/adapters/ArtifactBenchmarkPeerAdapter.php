<?php

declare(strict_types=1);

require_once __DIR__ . '/BenchmarkPeerAdapter.php';

/**
 * Loads one peer comparison target from an existing JSON artifact.
 */
final readonly class ArtifactBenchmarkPeerAdapter implements BenchmarkPeerAdapter
{
    public function __construct(private string $peerName, private string $path)
    {
    }

    #[Override]
    public function name() : string
    {
        return $this->peerName;
    }

    /**
     * @throws JsonException
     */
    #[Override]
    public function load() : array
    {
        if (! is_file(filename: $this->path)) {
            throw new RuntimeException(message: sprintf('Benchmark artifact [%s] does not exist.', $this->path));
        }

        $json = file_get_contents(filename: $this->path);
        if (! is_string(value: $json) || $json === '') {
            throw new RuntimeException(message: sprintf('Benchmark artifact [%s] could not be read.', $this->path));
        }

        $decoded = json_decode(json: $json, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
        if (! is_array(value: $decoded) || ! is_array(value: $decoded['results'] ?? null)) {
            throw new RuntimeException(message: sprintf('Benchmark artifact [%s] is invalid.', $this->path));
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
