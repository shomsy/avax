<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

/**
 * Structured context object for route resolution debugging and analysis.
 *
 * Provides comprehensive information about how and why a specific route was
 * selected, enabling advanced introspection, audit logging, and testing visibility.
 *
 * Replaces simple RouteDefinition returns with rich context for enterprise debugging.
 */
final readonly class RouteResolutionContext
{
    public function __construct(
        public RouteDefinition $route,
        public array           $parameters,
        public string|null     $matchedDomain,
        public float           $matchTimeMs,
        public array           $resolutionPath = [],
        public string|null     $failureReason = null,
    ) {}

    /**
     * Creates a successful resolution context.
     */
    public static function success(
        RouteDefinition $route,
        array           $parameters,
        string|null     $matchedDomain,
        float           $matchTimeMs,
        array           $resolutionPath = []
    ) : self
    {
        return new self(
            route         : $route,
            parameters    : $parameters,
            matchedDomain : $matchedDomain,
            matchTimeMs   : $matchTimeMs,
            resolutionPath: $resolutionPath
        );
    }

    /**
     * Creates a failed resolution context.
     *
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public static function failure(
        string $failureReason,
        float  $matchTimeMs,
        array  $resolutionPath = []
    ) : self
    {
        return new self(
            route         : new RouteDefinition(
                'GET',
                '/__resolution_failed__',
                static fn() => new \Avax\HTTP\Response\Classes\Response(
                    stream: \Avax\HTTP\Response\Classes\Stream::fromString('Route resolution failed'),
                    statusCode: 500,
                    headers: ['Content-Type' => 'text/plain']
                ),
                [],
                '__failed_route__'
            ),
            parameters    : [],
            matchedDomain : null,
            matchTimeMs   : $matchTimeMs,
            resolutionPath: $resolutionPath,
            failureReason : $failureReason
        );
    }

    /**
     * Gets a human-readable summary of the resolution process.
     */
    public function getSummary() : string
    {
        if (! $this->isSuccessful()) {
            return sprintf(
                'Route resolution failed: %s (%.3fms)',
                $this->failureReason,
                $this->matchTimeMs
            );
        }

        $domainInfo = $this->matchedDomain ? " on domain {$this->matchedDomain}" : '';
        $paramsInfo = ! empty($this->parameters) ? ' with params: ' . json_encode($this->parameters) : '';

        return sprintf(
            'Resolved %s %s%s%s in %.3fms',
            $this->route->method,
            $this->getResolvedPath(),
            $domainInfo,
            $paramsInfo,
            $this->matchTimeMs
        );
    }

    /**
     * Checks if resolution was successful.
     */
    public function isSuccessful() : bool
    {
        return $this->failureReason === null;
    }

    /**
     * Gets the matched route path with parameters substituted.
     */
    public function getResolvedPath() : string
    {
        $path = $this->route->path;

        foreach ($this->parameters as $key => $value) {
            $path = str_replace("{{$key}}", (string) $value, $path);
        }

        return $path;
    }

    /**
     * Converts context to array for logging/serialization.
     */
    public function toArray() : array
    {
        return [
            'successful'      => $this->isSuccessful(),
            'route'           => [
                'method' => $this->route->method,
                'path'   => $this->route->path,
                'name'   => $this->route->name,
                'domain' => $this->route->domain,
            ],
            'parameters'      => $this->parameters,
            'matched_domain'  => $this->matchedDomain,
            'match_time_ms'   => $this->matchTimeMs,
            'resolved_path'   => $this->getResolvedPath(),
            'resolution_path' => $this->resolutionPath,
            'failure_reason'  => $this->failureReason,
        ];
    }

    /**
     * Gets resolution path as human-readable steps.
     */
    public function getResolutionSteps() : array
    {
        return array_map(
            static fn(array $step) : string => sprintf(
                '[%s] %s',
                $step['timestamp'] ?? 'unknown',
                $step['description'] ?? 'unknown step'
            ),
            $this->resolutionPath
        );
    }
}