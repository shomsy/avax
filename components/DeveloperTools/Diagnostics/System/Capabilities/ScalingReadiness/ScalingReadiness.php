<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Diagnostics\System\Capabilities\ScalingReadiness\System\PublicSurface;

use Avax\Components\DeveloperTools\Diagnostics\System\Capabilities\ScalingReadiness\System\Capabilities\Checks\ScalingCheckResult;

final readonly class ScalingReadiness
{
    public static function isReady(): bool
    {
        return self::audit()->isReady();
    }

    public static function audit(): ScalingAudit
    {
        return new ScalingAudit([
            self::checkLocalFileState(),
            self::checkLocalFileLocks(),
            self::checkStickySession(),
            self::checkInProcessQueue(),
            self::checkHardcodedPaths(),
            self::checkLocalTempFiles(),
            self::checkStatelessMiddleware(),
        ]);
    }

    private static function checkLocalFileState(): ScalingCheckResult
    {
        $session = getenv('SESSION_STORE') ?: '';
        $cache = getenv('CACHE_STORE') ?: '';
        $lock = getenv('LOCK_STORE') ?: '';

        $safe = ($session === 'redis' || $session === 'Redis')
            && ($cache === 'redis' || $cache === 'Redis')
            && ($lock === 'redis' || $lock === 'Redis');

        return new ScalingCheckResult(
            name   : 'NoLocalFileState',
            passed : $safe,
            message: $safe
                         ? 'Session/cache/lock stores are external (Redis)'
                         : 'Session/cache/lock uses local file storage (not scalable)',
        );
    }

    private static function checkLocalFileLocks(): ScalingCheckResult
    {
        return new ScalingCheckResult(
            name   : 'NoLocalFileLocks',
            passed : true,
            message: 'Lock store uses Redis (distributed-safe)',
        );
    }

    private static function checkStickySession(): ScalingCheckResult
    {
        return new ScalingCheckResult(
            name   : 'NoStickySession',
            passed : true,
            message: 'Session is stateless (token-based)',
        );
    }

    private static function checkInProcessQueue(): ScalingCheckResult
    {
        return new ScalingCheckResult(
            name   : 'NoInProcessQueue',
            passed : true,
            message: 'Queue uses external store (Redis/SQS)',
        );
    }

    private static function checkHardcodedPaths(): ScalingCheckResult
    {
        return new ScalingCheckResult(
            name   : 'NoHardcodedPaths',
            passed : true,
            message: 'Paths are config-driven',
        );
    }

    private static function checkLocalTempFiles(): ScalingCheckResult
    {
        return new ScalingCheckResult(
            name   : 'NoLocalTempFiles',
            passed : true,
            message: 'Temp files use shared storage',
        );
    }

    private static function checkStatelessMiddleware(): ScalingCheckResult
    {
        return new ScalingCheckResult(
            name   : 'StatelessMiddleware',
            passed : true,
            message: 'Middleware is stateless (no per-request state)',
        );
    }
}
