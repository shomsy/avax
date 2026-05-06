<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\Foundation;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 * FacadeProvider for DI registration.
 * Registers facades with the container and sets up the container reference.
 */
class FacadeProvider
{
    /** @var array<string, class-string<BaseFacade>> */
    private array $facades = [];

    public function register(string $accessor, string $facadeClass): self
    {
        if (! is_subclass_of($facadeClass, BaseFacade::class)) {
            throw new InvalidArgumentException($facadeClass.' must extend '.BaseFacade::class);
        }

        $this->facades[$accessor] = $facadeClass;

        return $this;
    }

    public function boot(ContainerInterface $container): void
    {
        BaseFacade::setContainer($container);

        foreach ($this->facades as $accessor => $facadeClass) {
            $facadeClass::$accessor = $accessor;
        }
    }

    /** @return array<string, class-string<BaseFacade>> */
    public function getFacades(): array
    {
        return $this->facades;
    }

    public function clear(): void
    {
        BaseFacade::clearAllResolvedInstances();
        $this->facades = [];
    }
}
