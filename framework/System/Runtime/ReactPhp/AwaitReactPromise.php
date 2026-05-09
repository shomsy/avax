<?php

declare(strict_types=1);

namespace Avax\Framework\System\Runtime\ReactPhp;

use React\Promise\PromiseInterface;

/**
 * AwaitReactPromise — Bridges ReactPHP promises to synchronous execution.
 *
 * Only used internally by ReactPHP runtime, never exposed in App public API.
 */
final class AwaitReactPromise
{
    /**
     * Block until the promise resolves or rejects.
     *
     * @param PromiseInterface<mixed> $promise
     */
    public static function await(PromiseInterface $promise): mixed
    {
        $result = null;
        $error = null;
        $done = false;
        $loop = \React\EventLoop\Loop::get();

        $promise->then(
            onFulfilled: static function ($value) use (&$result, &$done): void {
                $result = $value;
                $done = true;
            },
            onRejected: static function ($reason) use (&$error, &$done): void {
                $error = $reason;
                $done = true;
            },
        );

        while (!$done) {
            $loop->run();
            usleep(1000);
        }

        if ($error !== null) {
            throw $error;
        }

        return $result;
    }
}
