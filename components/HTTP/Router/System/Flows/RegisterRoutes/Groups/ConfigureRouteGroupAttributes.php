<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Groups;

use InvalidArgumentException;

/**
 * Strategy-based configuration for route group attributes.
 */
final class ConfigureRouteGroupAttributes
{
    private array $strategies;

    public function __construct()
    {
        $this->strategies = [
            'prefix'     => static fn (RouteGroupContext $context, mixed $value) => $context->prefix = (string) $value,
            'middleware' => fn (RouteGroupContext $context, mixed $value) => $context->addMiddleware((array) $value),
            'domain'     => fn (RouteGroupContext $context, mixed $value) => $context->setDomain((string) $value),
            'name'       => static fn (RouteGroupContext $context, mixed $value) => $context->namePrefix = (string) $value,
            'authorize'  => fn (RouteGroupContext $context, mixed $value) => $context->setAuthorization((string) $value),
        ];
    }

    public function apply(array $attributes, RouteGroupContext $context) : void
    {
        foreach ($attributes as $attribute => $value) {
            if (! isset($this->strategies[$attribute])) {
                throw new InvalidArgumentException(sprintf('Unsupported route group attribute: %s', $attribute));
            }
            ($this->strategies[$attribute])($context, $value);
        }
    }
}
