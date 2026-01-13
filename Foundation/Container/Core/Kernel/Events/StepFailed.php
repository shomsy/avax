<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Events;

use Throwable;

/**
 * Step Failed Event
 *
 * Emitted when a pipeline step fails with an exception.
 *
 * @see docs/Core/Kernel/Events/StepFailed.md#quick-summary
 */
final readonly class StepFailed
{
    /**
     * @param string      $stepClass Class name of the step
     * @param float       $startedAt Start timestamp
     * @param float       $endedAt   End timestamp
     * @param float       $duration  Elapsed duration in seconds
     * @param string      $serviceId Service identifier being resolved
     * @param Throwable   $exception Thrown exception
     * @param string|null $traceId   Optional trace correlation ID
     *
     * @see docs/Core/Kernel/Events/StepFailed.md#method-__construct
     */
    public function __construct(
        public string      $stepClass,
        public float       $startedAt,
        public float       $endedAt,
        public float       $duration,
        public string      $serviceId,
        public Throwable   $exception,
        public string|null $traceId = null
    ) {}
}
