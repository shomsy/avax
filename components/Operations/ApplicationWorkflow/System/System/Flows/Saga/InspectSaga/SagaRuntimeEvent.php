<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\InspectSaga;

use DateTimeImmutable;

/**
 * SagaRuntimeEvent - diagnostics-safe event emitted by saga runtime inspection.
 */
final readonly class SagaRuntimeEvent
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string            $type,
        public string            $instanceId,
        public array             $payload = [],
        public ?string           $correlationId = null,
        public ?string           $causationId = null,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}
}
