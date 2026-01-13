<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Cache;

use JsonException;
use RuntimeException;

final readonly class RouteCacheManifest
{
    /** @param array<string, int> $files */
    private function __construct(
        private array  $files,
        private string $hash,
        private int    $generatedAt,
        private string $checksum
    ) {}

    public static function buildFromDirectory(string $baseDir) : self
    {
        $locator = new RouteFileLocator;
        $files   = [];

        foreach ($locator->discover(baseDir: $baseDir) as $file) {
            $files[$file->getPathname()] = $file->getMTime();
        }

        ksort($files);

        try {
            $hash     = sha1(string: json_encode($files, JSON_THROW_ON_ERROR));
            $checksum = hash('sha256', $hash . json_encode($files, JSON_THROW_ON_ERROR));
        } catch (JsonException $exception) {
            throw new RuntimeException(message: 'Unable to serialize route metadata.', previous: $exception);
        }

        return new self(files: $files, hash: $hash, generatedAt: time(), checksum: $checksum);
    }

    public static function fromFile(string $metadataPath) : self|null
    {
        if (! is_file(filename: $metadataPath) || ! is_readable(filename: $metadataPath)) {
            return null;
        }

        try {
            /** @var array{files: array<string, int>, hash: string, generated_at: int, checksum?: string, manifest_hash?: string} $payload */
            $payload = json_decode(file_get_contents(filename: $metadataPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        $manifest = new self(
            files      : $payload['files'] ?? [],
            hash       : $payload['hash'] ?? '',
            generatedAt: (int) ($payload['generated_at'] ?? 0),
            checksum   : $payload['checksum'] ?? ''
        );

        // Validate manifest integrity using stored hash
        if (isset($payload['manifest_hash'])) {
            $expectedHash = $manifest->generateManifestHash();
            if (! hash_equals($expectedHash, $payload['manifest_hash'])) {
                // Manifest has been tampered with
                return null;
            }
        }

        return $manifest;
    }

    public static function metadataPath(string $cachePath) : string
    {
        return "{$cachePath}.meta";
    }

    public function matches(self $other) : bool
    {
        // Check if this manifest is stale (generated before the other)
        if ($this->generatedAt < $other->generatedAt) {
            return false;
        }

        // Verify basic file integrity
        if ($this->hash !== $other->hash || $this->files !== $other->files) {
            return false;
        }

        // Verify checksum integrity if both have checksums
        if (! empty($this->checksum) && ! empty($other->checksum)) {
            return $this->checksum === $other->checksum;
        }

        // Fallback to basic comparison if checksums are missing (backward compatibility)
        return true;
    }

    public function writeTo(string $metadataPath) : void
    {
        $directory = dirname(path: $metadataPath);

        if (! is_dir(filename: $directory)) {
            mkdir(directory: $directory, permissions: 0777, recursive: true);
        }

        try {
            $data = $this->toArray();
            // Add SHA256 hash for trust boundary verification
            $data['manifest_hash'] = $this->generateManifestHash();

            file_put_contents(
                filename: $metadataPath,
                data    : json_encode(value: $data, flags: JSON_THROW_ON_ERROR)
            );
        } catch (JsonException $exception) {
            throw new RuntimeException(message: 'Unable to write route cache metadata.', previous: $exception);
        }
    }

    /**
     * Generate SHA256 hash of the manifest data for integrity verification.
     */
    private function generateManifestHash() : string
    {
        $data = [
            'files'        => $this->files,
            'hash'         => $this->hash,
            'generated_at' => $this->generatedAt,
            'checksum'     => $this->checksum,
        ];

        try {
            return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        } catch (JsonException $exception) {
            throw new RuntimeException(message: 'Unable to generate manifest hash.', previous: $exception);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'files'        => $this->files,
            'hash'         => $this->hash,
            'generated_at' => $this->generatedAt,
            'checksum'     => $this->checksum,
            'signature'    => $this->signature(),
        ];
    }

    /**
     * Creates an immutable signature for route integrity validation.
     *
     * This signature ensures that route definitions haven't changed between
     * environments or deployments, preventing cache poisoning attacks and
     * ensuring semantic equivalence across deployments.
     */
    public function signature() : string
    {
        $data = [
            'files'        => $this->files,
            'hash'         => $this->hash,
            'generated_at' => $this->generatedAt,
            'checksum'     => $this->checksum,
            'version'      => '2.1', // Schema version for future compatibility
        ];

        try {
            $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

            return hash('sha256', $json);
        } catch (JsonException $exception) {
            throw new RuntimeException(message: 'Unable to generate route manifest signature.', previous: $exception);
        }
    }

    /**
     * Validates signature against another manifest.
     *
     * Used to ensure immutable routing guarantees across environments.
     */
    public function validateSignature(self $other) : bool
    {
        return $this->signature() === $other->signature();
    }

    /**
     * Writes signature to a separate .sig file for integrity verification.
     */
    public function writeSignature(string $cachePath) : void
    {
        $signaturePath = $this->signaturePath(cachePath: $cachePath);
        $directory     = dirname(path: $signaturePath);

        if (! is_dir(filename: $directory)) {
            mkdir(directory: $directory, permissions: 0777, recursive: true);
        }

        file_put_contents(
            filename: $signaturePath,
            data    : $this->signature() . "\n"
        );
    }

    /**
     * Gets the signature file path for a cache file.
     */
    private function signaturePath(string $cachePath) : string
    {
        return "{$cachePath}.sig";
    }

    /**
     * Validates signature from .sig file.
     */
    public function validateSignatureFile(string $cachePath) : bool
    {
        $signaturePath = $this->signaturePath(cachePath: $cachePath);

        if (! is_file(filename: $signaturePath) || ! is_readable(filename: $signaturePath)) {
            return false; // No signature file = invalid
        }

        $storedSignature = trim(file_get_contents(filename: $signaturePath));

        return $this->signature() === $storedSignature;
    }

    /**
     * Generate hash for a RouteDefinition for change detection.
     *
     * Used to determine if route serialization can be skipped when
     * the route hasn't changed since last cache generation.
     */
    public function getRouteHash(\Avax\HTTP\Router\Routing\RouteDefinition $route) : string
    {
        $routeData = $route->toArray();

        // Remove runtime-specific fields that don't affect route logic
        unset($routeData['metadata']); // Metadata can change without affecting routing

        try {
            return hash('sha256', json_encode($routeData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        } catch (\JsonException $exception) {
            throw new \RuntimeException('Unable to generate route hash.', 0, $exception);
        }
    }
}