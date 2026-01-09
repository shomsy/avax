<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Events;

/**
 * Step Started Event
 *
 * Emitted when a pipeline step begins execution.
 *
 * @see docs_md/Core/Kernel/Events/StepStarted.md#quick-summary
 */
final readonly class StepStarted
{
    /**
     * @param string      $stepClass Class name of the starting step
     * @param float       $timestamp High-resolution start time
     * @param string      $serviceId Service identifier being resolved
     * @param string|null $traceId   Optional trace correlation ID
     *
     * @see docs_md/Core/Kernel/Events/StepStarted.md#method-__construct
     */
    public function __construct(
        public string      $stepClass,
        public float       $timestamp,
        public string      $serviceId,
        public string|null $traceId = null
    ) {}
}
