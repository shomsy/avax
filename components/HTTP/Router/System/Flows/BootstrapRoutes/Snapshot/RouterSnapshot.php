<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Flows\BootstrapRoutes\Snapshot;

use Avax\HTTP\Router\RouterRuntimeInterface;
use JsonException;
use RuntimeException;

/**
 * Router configuration snapshot for reproducibility and auditing.
 *
 * Creates immutable snapshots of router state for:
 * - Configuration auditing and compliance
 * - Reproducible deployments across environments
 * - Change tracking and rollback capabilities
 * - Governance and regulatory requirements
 */
final readonly class RouterSnapshot
{
    public string $checksum;
    public string $version;
    public string $environment;
    public string $createdAt;
    public array  $metadata;
    public array  $routes;

    public function __construct(
        array  $routes,
        array  $metadata,
        string $createdAt,
        string $environment,
        string $version,
        string $checksum,
    )
    {
        $this->routes      = $routes;
        $this->metadata    = $metadata;
        $this->createdAt   = $createdAt;
        $this->environment = $environment;
        $this->version     = $version;
        $this->checksum    = $checksum;
    }

    /**
     * Creates a snapshot from the current router state.
     */
    public static function capture(RouterRuntimeInterface $router, array $context = []) : self
    {
        $routes   = $router->allRoutes();
        $metadata = [
            'total_routes' => self::countTotalRoutes(routes: $routes),
            'methods'      => array_keys(array: $routes),
            'context'      => $context,
            'php_version'  => PHP_VERSION,
            'timestamp'    => time(),
        ];

        $data = [
            'routes'      => $routes,
            'metadata'    => $metadata,
            'environment' => $context['environment'] ?? config(key: 'app.env', default: 'unknown'),
            'version'     => $context['version'] ?? 'unknown',
        ];

        try {
            $json     = json_encode(value: $data, flags: JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $checksum = hash(algo: 'sha256', data: $json);
        } catch (JsonException $exception) {
            throw new RuntimeException(message: 'Unable to generate router snapshot.', previous: $exception);
        }

        return new self(
            routes     : $routes,
            metadata   : $metadata,
            createdAt  : date(format: 'c'),
            environment: $data['environment'],
            version    : $data['version'],
            checksum   : $checksum
        );
    }

    /**
     * Counts total routes across all methods.
     */
    private static function countTotalRoutes(array $routes) : int
    {
        return array_sum(array: array_map(callback: 'count', array: $routes));
    }

    /**
     * Loads snapshot from JSON file.
     */
    public static function loadFromFile(string $path) : self
    {
        if (! file_exists(filename: $path) || ! is_readable(filename: $path)) {
            throw new RuntimeException(message: "Snapshot file not found or not readable: {$path}");
        }

        try {
            $data = json_decode(json: file_get_contents(filename: $path), associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(message: "Invalid snapshot file: {$path}", previous: $exception);
        }

        return self::fromArray(data: $data);
    }

    /**
     * Creates snapshot from array data.
     */
    public static function fromArray(array $data) : self
    {
        return new self(
            routes     : $data['routes'] ?? [],
            metadata   : $data['metadata'] ?? [],
            createdAt  : $data['created_at'] ?? date(format: 'c'),
            environment: $data['environment'] ?? 'unknown',
            version    : $data['version'] ?? 'unknown',
            checksum   : $data['checksum'] ?? ''
        );
    }

    /**
     * Exports snapshot to JSON file.
     */
    public function exportToFile(string $path) : void
    {
        $data = $this->toArray();

        try {
            $json = json_encode(value: $data, flags: JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            file_put_contents(filename: $path, data: $json . "\n");
        } catch (JsonException $exception) {
            throw new RuntimeException(message: "Unable to export snapshot to {$path}.", previous: $exception);
        }
    }

    /**
     * Converts snapshot to array for serialization.
     */
    public function toArray() : array
    {
        return [
            'routes'      => $this->routes,
            'metadata'    => $this->metadata,
            'created_at'  => $this->createdAt,
            'environment' => $this->environment,
            'version'     => $this->version,
            'checksum'    => $this->checksum,
        ];
    }

    /**
     * Validates snapshot integrity using checksum.
     */
    public function validateIntegrity() : bool
    {
        $data = $this->toArray();
        unset($data['checksum']); // Remove checksum from validation data

        try {
            $json = json_encode(value: $data, flags: JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

            return hash(algo: 'sha256', data: $json) === $this->checksum;
        } catch (JsonException) {
            return false;
        }
    }

    /**
     * Compares this snapshot with another for differences.
     */
    public function diff(self $other) : array
    {
        $differences = [];

        // Compare route counts
        $thisCount  = $this->metadata['total_routes'];
        $otherCount = $other->metadata['total_routes'];

        if ($thisCount !== $otherCount) {
            $differences[] = "Route count changed: {$thisCount} → {$otherCount}";
        }

        // Compare routes by method
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $thisRoutes  = count(value: $this->routes[$method] ?? []);
            $otherRoutes = count(value: $other->routes[$method] ?? []);

            if ($thisRoutes !== $otherRoutes) {
                $differences[] = "{$method} routes: {$thisRoutes} → {$otherRoutes}";
            }
        }

        // Compare environment/version
        if ($this->environment !== $other->environment) {
            $differences[] = "Environment: {$this->environment} → {$other->environment}";
        }

        if ($this->version !== $other->version) {
            $differences[] = "Version: {$this->version} → {$other->version}";
        }

        return $differences;
    }

    /**
     * Gets a human-readable summary of the snapshot.
     */
    public function getSummary() : string
    {
        $lines = [
            "Router Snapshot Summary",
            "=======================",
            "Created: {$this->createdAt}",
            "Environment: {$this->environment}",
            "Version: {$this->version}",
            "Total Routes: {$this->metadata['total_routes']}",
            "Checksum: " . substr(string: $this->checksum, offset: 0, length: 16) . "...",
            "",
            "Routes by Method:",
        ];

        foreach ($this->metadata['methods'] as $method) {
            $count   = count(value: $this->routes[$method] ?? []);
            $lines[] = "  {$method}: {$count} routes";
        }

        return implode(separator: "\n", array: $lines);
    }
}