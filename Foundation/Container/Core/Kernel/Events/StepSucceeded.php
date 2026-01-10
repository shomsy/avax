<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Events;

/**
 * Step Succeeded Event
 *
 * Emitted when a pipeline step completes successfully.
 *
 * @see docs/Core/Kernel/Events/StepSucceeded.md#quick-summary
 */
final readonly class StepSucceeded
{
    /**
     * @param string      $stepClass Class name of the step
     * @param float       $startedAt Start timestamp
     * @param float       $endedAt   End timestamp
     * @param float       $duration  Elapsed duration in seconds
     * @param string      $serviceId Service identifier being resolved
     * @param string|null $traceId   Optional trace correlation ID
     *
     * @see docs/Core/Kernel/Events/StepSucceeded.md#method-__construct
     */
    public function __construct(
        public string      $stepClass,
        public float       $startedAt,
        public float       $endedAt,
        public float       $duration,
        public string      $serviceId,
        public string|null $traceId = null
    ) {}
}
