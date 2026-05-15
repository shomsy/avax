<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\PublicSurface;

use Avax\Components\Application\Pipeline\System\Capabilities\PipelineHooks\HookRegistry;
use Closure;
use RuntimeException;

/**
 * Pipeline — PublicSurface entry point for pipeline hook execution.
 *
 * Receives and delegates. Does not assemble or instantiate runtime services.
 * The HookRegistry must be configured during boot via setInstance().
 *
 * @see \Avax\Components\Application\Pipeline\System\Configuration\PipelineServiceProvider
 */
final class Pipeline
{
    private static ?HookRegistry $hookRegistry = null;

    /**
     * Configure the hook registry during boot.
     *
     * @throws RuntimeException if registry is already configured (prevents double-boot)
     */
    public static function setInstance(HookRegistry $registry): void
    {
        if (self::$hookRegistry !== null) {
            throw new RuntimeException('Pipeline registry is already configured.');
        }

        self::$hookRegistry = $registry;
    }

    public static function beforeRoute(Closure $handler): void
    {
        self::registry()->add('beforeRoute', $handler);
    }

    /**
     * @return HookRegistry The configured hook registry.
     * @throws RuntimeException if registry was not configured during boot.
     */
    private static function registry(): HookRegistry
    {
        if (self::$hookRegistry === null) {
            throw new RuntimeException(
                'Pipeline registry not configured. '
                . 'Ensure PipelineServiceProvider is registered and booted.'
            );
        }

        return self::$hookRegistry;
    }

    public static function afterRoute(Closure $handler): void
    {
        self::registry()->add('afterRoute', $handler);
    }

    public static function beforeController(Closure $handler): void
    {
        self::registry()->add('beforeController', $handler);
    }

    public static function afterController(Closure $handler): void
    {
        self::registry()->add('afterController', $handler);
    }

    public static function beforeResponse(Closure $handler): void
    {
        self::registry()->add('beforeResponse', $handler);
    }

    public static function afterResponse(Closure $handler): void
    {
        self::registry()->add('afterResponse', $handler);
    }

    public static function onException(Closure $handler): void
    {
        self::registry()->add('onException', $handler);
    }

    public static function onTerminate(Closure $handler): void
    {
        self::registry()->add('onTerminate', $handler);
    }

    public static function execute(string $hook, mixed $data = null): mixed
    {
        return self::registry()->execute($hook, $data);
    }

    /**
     * @return array<string, list<Closure>>
     */
    public static function hooks(): array
    {
        return self::registry()->all();
    }

    /**
     * Reset the static hook registry. Required for test isolation and long-lived workers.
     */
    public static function reset(): void
    {
        self::$hookRegistry = null;
    }
}
