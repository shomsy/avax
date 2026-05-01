<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings;

use Avax\Components\Application\Container\System\ContainerInterface;

/**
 * Explicit contract for object decorators applied after service construction.
 */
interface DecoratorInterface
{
    /**
     * Decorates one resolved service instance.
     */
    public function decorate(mixed $instance, ?ContainerInterface $container = null): mixed;
}
