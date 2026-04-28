<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Stringable;

/**
 * Registry for pluggable route matching strategies.
 */
final class RouteMatcherRegistry
{
    private array $matchers = [];

    public static function withDefaults(LoggerInterface|null $logger = null) : self
    {
        $registry = new self();
        $logger   ??= new class implements LoggerInterface {
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

        $registry->register('domain', new DomainAwareMatcher(
            baseMatcher: new RouteMatcher(logger: $logger)
        ));

        return $registry;
    }

    public function register(string $key, RouteMatcherInterface $matcher) : void
    {
        $this->matchers[$key] = $matcher;
    }

    public function get(string $key) : RouteMatcherInterface
    {
        if (! isset($this->matchers[$key])) {
            throw new InvalidArgumentException("Route matcher '{$key}' is not registered.");
        }

        return $this->matchers[$key];
    }

    public function has(string $key) : bool
    {
        return isset($this->matchers[$key]);
    }

    public function keys() : array
    {
        return array_keys($this->matchers);
    }
}
