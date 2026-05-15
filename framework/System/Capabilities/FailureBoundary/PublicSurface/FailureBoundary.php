<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\PublicSurface;

use Avax\Framework\System\Capabilities\FailureBoundary\Configuration\Builders\BuildFailureBoundary;
use Avax\Framework\System\Capabilities\FailureBoundary\Flows\RunProtectedAction\RunProtectedAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Closure;
use Psr\Http\Message\RequestInterface;

/**
 * FailureBoundary — Static facade for the failure boundary component.
 *
 * Usage:
 * $result = FailureBoundary::run(
 *     action: fn () => $this->users->create($data),
 *     context: FailureContext::forHttp($request, UserController::class, 'create'),
 * );
 */
final class FailureBoundary
{
    private static ?RunProtectedAction $instance = null;

    public static function setInstance(RunProtectedAction $instance): void
    {
        self::$instance = $instance;
    }

    /**
     * Reset the cached instance. Required for long-lived runtimes (worker mode).
     */
    public static function reset() : void
    {
        self::$instance = null;
    }

    public static function getInstance(): RunProtectedAction
    {
        if (self::$instance === null) {
            self::$instance = (new BuildFailureBoundary())->build();
        }
        return self::$instance;
    }

    public static function run(Closure $action, FailureContext $context): mixed
    {
        return self::getInstance()->run($action, $context);
    }

    public static function forHttp(Closure $action, RequestInterface $request, string $targetClass = '', string $targetMethod = ''): mixed
    {
        return self::run(
            action: $action,
            context: FailureContext::forHttp($request, $targetClass, $targetMethod),
        );
    }
}
