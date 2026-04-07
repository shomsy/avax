<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Injection\Attributes;

use Attribute;

/**
 * Dependency Injection Marker Attribute
 *
 * Marks properties, methods, or parameters for automatic dependency injection.
 * When applied, the container will resolve and inject the specified dependency
 * during object construction or method invocation.
 *
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
final class Inject
{
    public function __construct(
        public string|null $abstract = null
    ) {}
}
