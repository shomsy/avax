<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Capabilities\Drivers;

final class S3 implements StorageAdapter
{
    /** @var array<string, string> */
    private array $objects = [];

    public function __construct(private readonly array $config = []) {}

    public function put(string $path, string $contents): bool
    {
        $this->objects[$this->normalize(path: $path)] = $contents;

        return true;
    }

    private function normalize(string $path): string
    {
        return trim(string: str_replace(search: '\\', replace: '/', subject: $path), characters: '/');
    }

    public function get(string $path): ?string
    {
        return $this->objects[$this->normalize(path: $path)] ?? null;
    }

    public function delete(string $path): bool
    {
        unset($this->objects[$this->normalize(path: $path)]);

        return true;
    }

    public function exists(string $path): bool
    {
        return array_key_exists(key: $this->normalize(path: $path), array: $this->objects);
    }

    public function signedUrl(string $path, int $expiresInSeconds = 3600): string
    {
        $expiresAt = time() + $expiresInSeconds;
        $normalizedPath = $this->normalize(path: $path);
        $secret    = $this->config['secret'] ?? 'avax-s3-storage';
        $signature = hash_hmac(algo: 'sha256', data: $normalizedPath . '|' . $expiresAt, key: $secret);

        return $this->url(path: $normalizedPath) . '?X-Amz-Expires=' . $expiresInSeconds . '&X-Amz-Date=' . $expiresAt . '&X-Amz-Signature=' . $signature;
    }

    public function url(string $path): string
    {
        $bucket = $this->config['bucket'] ?? 'avax';
        $region = $this->config['region'] ?? 'us-east-1';

        return sprintf(
            'https://%s.s3.%s.amazonaws.com/%s',
            rawurlencode(string: (string) $bucket),
            rawurlencode(string: (string) $region),
            str_replace(search: '%2F', replace: '/', subject: rawurlencode(string: $this->normalize(path: $path))),
        );
    }

    public function size(string $path): int
    {
        return strlen(string: $this->objects[$this->normalize(path: $path)] ?? '');
    }

    public function makeDirectory(string $directory): bool
    {
        return true;
    }

    public function deleteDirectory(string $directory): bool
    {
        foreach ($this->files(directory: $directory) as $file) {
            unset($this->objects[$file]);
        }

        return true;
    }

    public function files(string $directory = ''): array
    {
        $prefix = $this->normalize(path: $directory);
        $files = array_keys(array: $this->objects);

        if ($prefix === '') {
            sort(array: $files);

            return $files;
        }

        $filtered = array_values(array_filter(
            array   : $files,
            callback: static fn (string $file) : bool => str_starts_with(haystack: $file, needle: rtrim(string: $prefix, characters: '/') . '/'),
        ));
        sort(array: $filtered);

        return $filtered;
    }
}
