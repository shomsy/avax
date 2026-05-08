<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Foundation;

final readonly class WorkerResult
{
    public function __construct(
        public string           $workerId,
        public mixed            $value,
        public bool             $success,
        public ?ParallelFailure $failure,
    ) {}

    public static function success(string $workerId, mixed $value) : self
    {
        return new self(workerId: $workerId, value: $value, success: true, failure: null);
    }

    public static function failure(string $workerId, ParallelFailure $failure) : self
    {
        return new self(workerId: $workerId, value: null, success: false, failure: $failure);
    }

    public function isSuccess() : bool
    {
        return $this->success;
    }

    public function isFailure() : bool
    {
        return ! $this->success;
    }
}
