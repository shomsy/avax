<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Boot;

use Avax\HTTP\Router\RouterInterface;
use Avax\Logging\ErrorHandler;
use Closure;
use Throwable;
use function Avax\Container\Operate\Boot\config;

/**
 * Application kernel responsible for preparing middleware and wiring HTTP dependencies.
 *
 * This class exposes the middleware resolution strategy used during kernel boot,
 * ensuring that configuration failures or missing setups do not break the lifecycle.
 *
 * @see docs/Features/Operate/Boot/Kernel.md#quick-summary
 */
final readonly class Kernel
{
    private Closure $configResolver;

    /**
     * Initialize the application kernel with essential HTTP components.
     *
     * @param RouterInterface $router         Router implementation responsible for request dispatch.
     * @param ErrorHandler    $errorHandler   Centralized error handler for early lifecycle errors.
     * @param callable|null   $configResolver Optional resolver that provides middleware configuration data.
     *
     * @see docs/Features/Operate/Boot/Kernel.md#method-__construct
     */
    public function __construct(
        private RouterInterface $router,
        private ErrorHandler    $errorHandler,
        callable|null           $configResolver = null,
    )
    {
        $this->configResolver = $configResolver !== null
            ? Closure::fromCallable(callback: $configResolver)
            : $this->defaultConfigResolver();
    }

    /**
     * Build the default configuration resolver that safely reads kernel metadata from config files.
     */
    private function defaultConfigResolver() : Closure
    {
        return static fn() => config(key: 'kernel.middlewares', default: []);
    }

    /**
     * Resolve the middleware list that should be applied to the HTTP kernel.
     *
     * Any configuration errors are caught and result in an empty array,
     * mirroring the legacy safety net expected by the tests.
     *
     * @see docs/Features/Operate/Boot/Kernel.md#method-resolveconfiguredmiddlewares
     */
    public function resolveConfiguredMiddlewares() : array
    {
        try {
            $middlewares = ($this->configResolver)();
        } catch (Throwable) {
            return [];
        }

        return is_array($middlewares) ? $middlewares : [];
    }
}
