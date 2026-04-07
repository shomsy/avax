<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Resolution\Pipeline\Events;

/**
 * Step Succeeded Event
 *
 * Emitted when a pipeline step completes successfully.
 *
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
