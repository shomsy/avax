<?php
declare(strict_types=1);

namespace Tests\Operations;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\Workflow;
use Exception;
use PHPUnit\Framework\TestCase;

final class SagaTest extends TestCase
{
    public function test_saga_executes_all_steps_on_success() : void
    {
        $workflow = new Workflow();
        $def      = new SagaDefinition();

        $steps = 0;
        $def->step('step1', function () use (&$steps) { $steps++; });
        $def->step('step2', function () use (&$steps) { $steps++; });

        $workflow->runSaga($def);

        $this->assertEquals(2, $steps);
    }

    public function test_saga_runs_compensations_on_failure() : void
    {
        $workflow = new Workflow();
        $def      = new SagaDefinition();

        $step1Done        = false;
        $step1Compensated = false;

        $def->step(
            'step1',
            function () use (&$step1Done) { $step1Done = true; },
            function () use (&$step1Compensated) { $step1Compensated = true; }
        );

        $def->step('step2', function () {
            throw new Exception("Fail at step 2");
        });

        try {
            $workflow->runSaga($def);
        } catch (Exception $e) {
            $this->assertEquals("Fail at step 2", $e->getMessage());
        }

        $this->assertTrue($step1Done);
        $this->assertTrue($step1Compensated);
    }
}
