<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RouteIntelligence;

/**
 * Represents route information for analysis.
 */
final readonly class RouteInfo
{
    public function __construct(
        public string      $method,
        public string      $pattern,
        public string      $handler,
        public array       $middleware = [],
        public string|null $name = null,
        public array       $constraints = [],
    ) {}

    public function hasParameters() : bool
    {
        return preg_match('/\{(\w+)\??\}/', $this->pattern) === 1;
    }

    /**
     * @return list<string>
     */
    public function parameters() : array
    {
        preg_match_all('/\{(\w+)\??\}/', $this->pattern, $matches);

        return $matches[1] ?? [];
    }

    public function fingerprint() : string
    {
        return "{$this->method}:{$this->pattern}";
    }
}
