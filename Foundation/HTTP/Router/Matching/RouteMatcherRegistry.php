<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Matching;

use Avax\HTTP\Router\Routing\DomainAwareMatcher;
use Avax\HTTP\Router\Routing\RouteMatcher;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Stringable;

/**
 * Registry for pluggable route matching strategies.
 *
 * Enables enterprise flexibility by allowing different matching algorithms:
 * - 'domain': DomainAwareMatcher (default) - validates host/domain constraints
 * - 'trie': TrieMatcher - efficient prefix tree for high-volume routing
 * - 'regex': RegexMatcher - full regex pattern matching
 * - 'hybrid': HybridMatcher - combines multiple strategies
 *
 * Usage:
 * $registry = new RouteMatcherRegistry();
 * $registry->register('custom', new CustomMatcher());
 * $router->setMatcher($registry->get('custom'));
 */
final class RouteMatcherRegistry
{
    /** @var array<string, RouteMatcherInterface> */
    private array $matchers = [];

    /**
     * Creates a registry with default matchers pre-registered.
     */
    public static function withDefaults(LoggerInterface|null $logger = null) : self
    {
        $registry = new self();

        // Use NullLogger if no logger provided
        $logger ??= new class implements LoggerInterface {
            public function emergency(string|Stringable $message, array $context = []) : void {}

            public function alert(string|Stringable $message, array $context = []) : void {}

            public function critical(string|Stringable $message, array $context = []) : void {}

            public function error(string|Stringable $message, array $context = []) : void {}

            public function warning(string|Stringable $message, array $context = []) : void {}

            public function notice(string|Stringable $message, array $context = []) : void {}

            public function info(string|Stringable $message, array $context = []) : void {}

            public function debug(string|Stringable $message, array $context = []) : void {}

            public function log($level, string|Stringable $message, array $context = []) : void {}
        };

        // Register default matchers
        $registry->register(key: 'domain', matcher: new DomainAwareMatcher(
            baseMatcher: new RouteMatcher(logger: $logger)
        ));

        // Additional matchers can be registered here as they are implemented
        // $registry->register('trie', new TrieMatcher());
        // $registry->register('regex', new RegexMatcher());

        return $registry;
    }

    /**
     * Registers a matcher strategy with a unique key.
     */
    public function register(string $key, RouteMatcherInterface $matcher) : void
    {
        $this->matchers[$key] = $matcher;
    }

    /**
     * Retrieves a registered matcher by key.
     *
     * @throws InvalidArgumentException If matcher key is not registered
     */
    public function get(string $key) : RouteMatcherInterface
    {
        if (! isset($this->matchers[$key])) {
            throw new InvalidArgumentException(message: "Route matcher '{$key}' is not registered. Available: " . implode(', ', array_keys($this->matchers)));
        }

        return $this->matchers[$key];
    }

    /**
     * Checks if a matcher key is registered.
     */
    public function has(string $key) : bool
    {
        return isset($this->matchers[$key]);
    }

    /**
     * Returns all registered matcher keys.
     *
     * @return string[]
     */
    public function keys() : array
    {
        return array_keys($this->matchers);
    }

    /**
     * Removes a registered matcher.
     */
    public function unregister(string $key) : void
    {
        unset($this->matchers[$key]);
    }

    /**
     * Clears all registered matchers.
     */
    public function clear() : void
    {
        $this->matchers = [];
    }

    /**
     * Gets the number of registered matchers.
     */
    public function count() : int
    {
        return count($this->matchers);
    }
}