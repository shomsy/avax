<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga;

/**
 * SagaStartCommand - explicit request to create a saga instance, optionally protected by an idempotency key.
 */
final readonly class SagaStartCommand
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $definitionName,
        public array $payload = [],
        public ?string $commandKey = null,
        public ?string $correlationId = null,
    ) {}
}
