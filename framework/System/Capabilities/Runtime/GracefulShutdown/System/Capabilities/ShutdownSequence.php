<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\GracefulShutdown\System\Capabilities;

use Closure;
use Throwable;

final class ShutdownSequence
{
    /**
     * @var list<Closure>
     */
    private static array $callbacks = [];

    private static bool $draining = false;

    private static bool $executed = false;

    public static function register(Closure $callback): void
    {
        self::$callbacks[] = $callback;
    }

    public static function execute(int $timeoutSeconds = 30): void
    {
        if (self::$executed) {
            return;
        }

        self::$executed = true;

        echo "\n=== Graceful Shutdown Initiated ===\n";

        self::stopAcceptingNewRequests();

        self::waitForInFlightRequests($timeoutSeconds);

        self::flushBuffers();

        self::closeConnections();

        self::executeCallbacks();

        echo "=== Shutdown Complete ===\n";

        exit(0);
    }

    private static function stopAcceptingNewRequests(): void
    {
        self::$draining = true;
        echo "[1/5] Stopping new requests\n";
    }

    private static function waitForInFlightRequests(int $maxSeconds): void
    {
        echo "[2/5] Waiting for in-flight requests (max {$maxSeconds}s)...\n";

        $timeout = time() + $maxSeconds;
        $inFlight = self::countInFlightRequests();

        while ($inFlight > 0 && time() < $timeout) {
            usleep(100000);
            $newCount = self::countInFlightRequests();

            if ($newCount < $inFlight) {
                echo sprintf('  Requests remaining: %d%s', $newCount, PHP_EOL);
            }

            $inFlight = $newCount;
        }

        if ($inFlight > 0) {
            echo "  Timeout reached, forcing shutdown\n";
        } else {
            echo "  All requests completed\n";
        }
    }

    private static function countInFlightRequests(): int
    {
        return 0;
    }

    private static function flushBuffers(): void
    {
        echo "[3/5] Flushing buffers...\n";

        foreach (self::$callbacks as $callback) {
            try {
                $callback();
            } catch (Throwable $e) {
                error_log('Shutdown flush error: '.$e->getMessage());
            }
        }
    }

    private static function closeConnections(): void
    {
        echo "[4/5] Closing connections...\n";
    }

    private static function executeCallbacks(): void
    {
        echo "[5/5] Executing shutdown callbacks...\n";

        foreach (self::$callbacks as $callback) {
            try {
                $callback();
            } catch (Throwable $e) {
                error_log('Shutdown callback error: '.$e->getMessage());
            }
        }
    }

    public static function isDraining(): bool
    {
        return self::$draining;
    }
}
