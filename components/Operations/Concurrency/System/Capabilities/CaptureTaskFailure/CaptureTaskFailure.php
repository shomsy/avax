<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Capabilities\CaptureTaskFailure;

use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentFailure;
use Throwable;

final readonly class CaptureTaskFailure
{
    /**
     * @param array<string|int, Throwable> $exceptions
     *
     * @return list<ConcurrentFailure>
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
    ) : ConcurrentFailure
    {
        return new ConcurrentFailure(
            name    : $name,
            message : $exception->getMessage(),
            code    : $exception->getCode(),
            previous: $exception->getPrevious(),
        );
    }
}
