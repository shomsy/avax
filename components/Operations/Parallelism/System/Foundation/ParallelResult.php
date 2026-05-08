<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Foundation;

use Avax\Components\Operations\Parallelism\System\Foundation\Failure\ParallelException;

final readonly class ParallelResult
{
    /**
     * @param array<string|int, mixed> $values
     * @param list<ParallelFailure>    $failures
     */
    public function __construct(
        public array $values,
        public array $failures,
        public int   $startedWorkers,
        public int   $finishedWorkers,
        public int   $failedWorkers,
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
     * @return list<ParallelFailure>
     */
    public function failures() : array
    {
        return $this->failures;
    }

    public function hasFailures() : bool
    {
        return $this->failedWorkers > 0;
    }

    public function throwIfFailed() : void
    {
        if (! $this->successful()) {
            $count    = count($this->failures);
            $first    = $this->failures[0] ?? null;
            $message  = $count === 1
                ? ($first?->getMessage() ?? 'Worker failed')
                : "{$count} workers failed";
            $code     = $first?->getCode() ?? 1;
            $previous = $first?->getPrevious();
            throw new ParallelException(message: $message, code: $code, previous: $previous);
        }
    }

    public function successful() : bool
    {
        return $this->failedWorkers === 0;
    }
}
