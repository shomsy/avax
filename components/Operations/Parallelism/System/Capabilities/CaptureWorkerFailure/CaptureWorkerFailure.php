<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Capabilities\CaptureWorkerFailure;

use Avax\Components\Operations\Parallelism\System\Foundation\ParallelFailure;
use Throwable;

final readonly class CaptureWorkerFailure
{
    /**
     * @param array<string|int, Throwable> $exceptions
     *
     * @return list<ParallelFailure>
     */
    public function captureAll(array $exceptions) : array
    {
        $failures = [];

        foreach ($exceptions as $name => $exception) {
            $failures[] = $this->capture($name, $exception);
        }

        return $failures;
    }

    public function capture(
        string|int $name,
        Throwable $exception,
    ) : ParallelFailure
    {
        return new ParallelFailure(
            name    : $name,
            message : $exception->getMessage(),
            code    : $exception->getCode(),
            previous: $exception->getPrevious(),
        );
    }
}
