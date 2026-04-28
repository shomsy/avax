<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState;

use DateTimeImmutable;

/**
 * SagaEvent - ordered diagnostic and recovery evidence for a saga instance.
 */
final readonly class SagaEvent
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string            $id,
        public string            $instanceId,
        public string            $type,
        public array             $payload = [],
        public string|null       $correlationId = null,
        public string|null       $causationId = null,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}
}
