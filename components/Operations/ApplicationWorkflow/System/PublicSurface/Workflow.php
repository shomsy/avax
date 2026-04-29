<?php
declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga\SagaDefinition;
use Throwable;

final readonly class Workflow
{
    public function runSaga(SagaDefinition $definition): void
    {
        $completed = [];
        try {
            foreach ($definition->getSteps() as $step) {
                ($step->action)();
                $completed[] = $step;
            }
        } catch (Throwable $e) {
            foreach (array_reverse($completed) as $step) {
                if ($step->compensation) {
                    ($step->compensation)();
                }
            }
            throw $e;
        }
    }
}
