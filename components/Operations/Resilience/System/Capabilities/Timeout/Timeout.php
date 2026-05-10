<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Timeout;

use Avax\Components\Operations\Resilience\System\Foundation\Failure\OperationTimedOut\OperationTimedOut;
use Closure;

/**
 * Timeout — wraps operations with timeout enforcement.
 *
 * Mode selection:
 * - pcntl mode: uses pcntl_alarm + SIGALRM for pre-emptive timeout (CLI/long-running only).
 * - Elapsed mode: measures elapsed time after operation completes (post-hoc).
 *   This is the default and only option when pcntl is unavailable.
 *
 * PHP limitation:
 * No PHP mechanism can interrupt a blocking I/O call mid-execution without pcntl.
 * For HTTP/database calls, set timeouts on the underlying client/driver.
 * This class enforces a boundary around the caller's operation, not around external I/O.
 */
final class Timeout
{
    private static ?bool $pcntlAvailable = null;

    public function __construct(
        public int $timeoutMs = 5000,
        private bool $usePcntl = false,
    ) {
        if ($this->usePcntl && !self::isPcntlAvailable()) {
            $this->usePcntl = false;
        }
    }

    /**
     * @template TResult
     * @param Closure(): TResult $operation
     *
     * @return TResult
     *
     * @throws OperationTimedOut
     */
    public function run(Closure $operation) : mixed
    {
        if ($this->usePcntl) {
            return $this->runWithPcntl($operation);
        }

        return $this->runElapsed($operation);
    }

    /**
     * Pre-emptive timeout using pcntl alarm.
     * Only available in CLI environments with pcntl extension.
     *
     * @template TResult
     * @param Closure(): TResult $operation
     *
     * @return TResult
     */
    private function runWithPcntl(Closure $operation) : mixed
    {
        $seconds = (int) ceil($this->timeoutMs / 1000);
        if ($seconds < 1) {
            $seconds = 1;
        }

        if (!self::isPcntlAvailable()) {
            // Should never reach here since constructor guards, but fall back safely.
            return $this->runElapsed($operation);
        }

        $sigAlarm = SIGALRM;
        $sigDfl = SIG_DFL;

        $handler = static function () : void {
            throw new OperationTimedOut('Operation timed out (pcntl alarm).');
        };

        $previous = \pcntl_signal($sigAlarm, $handler);

        try {
            \pcntl_alarm($seconds);
            $result = $operation();
            \pcntl_alarm(0);

            return $result;
        } catch (OperationTimedOut $e) {
            throw $e;
        } finally {
            \pcntl_alarm(0);
            $restoreHandler = \is_callable($previous) || \is_int($previous) ? $previous : (int) $sigDfl;
            \pcntl_signal($sigAlarm, $restoreHandler);
        }
    }

    /**
     * Post-hoc elapsed time check.
     * The operation runs to completion; if it exceeded the timeout, OperationTimedOut is thrown.
     *
     * @template TResult
     * @param Closure(): TResult $operation
     *
     * @return TResult
     */
    private function runElapsed(Closure $operation) : mixed
    {
        $startTime = hrtime(true);

        $result = $operation();

        $elapsedMs = (hrtime(true) - $startTime) / 1_000_000;

        if ($elapsedMs > $this->timeoutMs) {
            throw new OperationTimedOut(
                \sprintf(
                    'Operation timed out after %dms (limit: %dms).',
                    (int) round($elapsedMs),
                    $this->timeoutMs,
                ),
            );
        }

        return $result;
    }

    public function withTimeout(int $ms) : self
    {
        return new self($ms, $this->usePcntl);
    }

    /**
     * Create a Timeout that uses pcntl alarm for pre-emptive timeout.
     * Returns an elapsed-mode Timeout if pcntl is unavailable.
     */
    public static function preEmptive(int $timeoutMs = 5000) : self
    {
        return new self($timeoutMs, usePcntl: true);
    }

    /**
     * Create a Timeout that checks elapsed time after operation completes.
     */
    public static function elapsed(int $timeoutMs = 5000) : self
    {
        return new self($timeoutMs, usePcntl: false);
    }

    public static function isPcntlAvailable() : bool
    {
        if (self::$pcntlAvailable === null) {
            self::$pcntlAvailable = \extension_loaded('pcntl') && PHP_SAPI === 'cli' && defined('SIGALRM');
        }

        return self::$pcntlAvailable;
    }
}
