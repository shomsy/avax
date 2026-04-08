<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Bindings;

use Avax\Container\ContainerInterface;

/**
 * Explicit contract for object decorators applied after service construction.
 */
interface DecoratorInterface
{
    /**
     * Decorates one resolved service instance.
     */
    public function decorate(mixed $instance, ContainerInterface|null $container = null) : mixed;
}
