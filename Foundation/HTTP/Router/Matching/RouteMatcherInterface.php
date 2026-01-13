<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Matching;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\RouteDefinition;

/**
 * Strategy pattern interface for route matching algorithms.
 *
 * Enables pluggable matching strategies:
 * - DomainAwareMatcher: Host/domain constraint validation
 * - TrieMatcher: Efficient prefix tree matching
 * - RegexMatcher: Full regex pattern matching
 * - HybridMatcher: Combination strategies
 */
interface RouteMatcherInterface
{
    /**
     * Matches a request against registered routes.
     *
     * @param array<string, array<string, RouteDefinition>> $routes  Routes grouped by HTTP method and path
     * @param Request                                       $request The incoming HTTP request
     *
     * @return array{RouteDefinition, array<string, string>}|null
     *         Returns [route, parameters] or null if no match
     */
    public function match(array $routes, Request $request) : array|null;
}