<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga\Store;

interface SagaStoreInterface
{
    public function save(string $sagaId, string $status, array $context): void;
    public function get(string $sagaId): ?array;
    public function updateStatus(string $sagaId, string $status): void;
}
