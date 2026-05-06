<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

final readonly class RebuildSagaState
{
    public function rebuild(
        string $sagaId,
        array $events,
    ): array {
        $data = [];
        $completedSteps = [];
        $stepResults = [];
        $currentStepIndex = 0;
        $currentStepName = null;

        foreach ($events as $event) {
            $type = $event['type'] ?? '';

            switch ($type) {
                case 'saga_started':
                    $data = $event['payload'] ?? [];
                    $currentStepIndex = 0;

                    break;

                case 'step_completed':
                    $stepName = $event['step_name'] ?? '';
                    $completedSteps[] = $stepName;
                    $stepResults[$stepName] = $event['payload'] ?? [];
                    $currentStepName = $stepName;
                    $currentStepIndex++;

                    break;

                case 'step_failed':

                case 'saga_completed':
                case 'saga_compensated':
                    break 2;
            }
        }

        return [
            'data' => $data,
            'completed_steps' => $completedSteps,
            'step_results' => $stepResults,
            'current_step_index' => $currentStepIndex,
            'current_step_name' => $currentStepName,
        ];
    }
}
