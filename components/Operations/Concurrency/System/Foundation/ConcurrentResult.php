<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Foundation;

use Avax\Components\Operations\Concurrency\System\Foundation\Failure\ConcurrencyException;

final readonly class ConcurrentResult
{
    /**
     * @param array<string|int, mixed> $values
     * @param list<ConcurrentFailure>  $failures
     */
    public function __construct(
        public array $values,
        public array $failures,
        public int   $startedTasks,
        public int   $finishedTasks,
        public int   $failedTasks,
        public int   $cancelledTasks,
    ) {}

    public function value(string|int $name) : mixed
    {
        return $this->values[$name] ?? null;
    }

    /**
     * @return array<string|int, mixed>
     */
    public function values() : array
    {
        return $this->values;
    }

    /**
     * @return list<ConcurrentFailure>
     */
    public function failures() : array
    {
        return $this->failures;
    }

    public function hasFailures() : bool
    {
        return $this->failedTasks > 0 || $this->cancelledTasks > 0;
    }

    public function throwIfFailed() : void
    {
        if (! $this->successful()) {
            $count    = count($this->failures);
            $first    = $this->failures[0] ?? null;
            $message  = $count === 1
                ? ($first?->getMessage() ?? 'Task failed')
                : "{$count} tasks failed";
            $code     = $first?->getCode() ?? 1;
            $previous = $first?->getPrevious();
            throw new ConcurrencyException(message: $message, code: $code, previous: $previous);
        }
    }

    public function successful() : bool
    {
        return $this->failedTasks === 0 && $this->cancelledTasks === 0;
    }
}
