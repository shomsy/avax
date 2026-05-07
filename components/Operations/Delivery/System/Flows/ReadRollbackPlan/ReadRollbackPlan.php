<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Flows\ReadRollbackPlan;

use Avax\Components\Operations\Delivery\System\Capabilities\Manifest\RollbackPlan;

final readonly class ReadRollbackPlan
{
    /**
     * @return array{steps: int, reversible: bool}
     */
    public function read(RollbackPlan $plan) : array
    {
        $data = $plan->toArray();

        return [
            'steps'      => count($data['steps']),
            'reversible' => $plan->canRollback(),
        ];
    }
}
