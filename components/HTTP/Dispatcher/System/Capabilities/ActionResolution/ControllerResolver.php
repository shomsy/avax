<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Dispatcher\System\Capabilities\ActionResolution;

use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * ControllerResolver - Instantiates controller classes, preferably via DI container.
 */
final readonly class ControllerResolver
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function resolve(string $className) : object
    {
        if (! class_exists($className)) {
            throw new RuntimeException("Controller class '{$className}' does not exist.");
        }

        if ($this->container->has($className)) {
            return $this->container->get($className);
        }

        return new $className();
    }
}
