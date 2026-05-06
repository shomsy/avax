<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

final readonly class FindRecoverableSaga
{
    public function __construct(private object $store)
    {
    }

    public function find(?string $tenantId = null, int $limit = 100): array
    {
        $recoverable = [];
        $allSagas = $this->store->all();

        foreach ($allSagas as $sagaId => $data) {
            if ($this->isRecoverable(data: $data, tenantId: $tenantId)) {
                $recoverable[] = $sagaId;
                if (count($recoverable) >= $limit) {
                    break;
                }
            }
        }

        return $recoverable;
    }

    private function isRecoverable(array $data, ?string $tenantId): bool
    {
        if (($data['status'] ?? '') !== 'failed') {
            return false;
        }

        if ($tenantId !== null && ($data['tenant_id'] ?? '') !== $tenantId) {
            return false;
        }

        $currentStep = $data['current_step_name'] ?? null;

        return $currentStep !== null;
    }
}
