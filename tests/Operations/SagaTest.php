<?php

declare(strict_types=1);

namespace Avax\Tests\Operations;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaExecutor\SagaExecutor;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaLifecycle\ValidateSagaTransition;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaStore\InMemorySagaStore;
use Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\Workflow;
use Exception;
use PHPUnit\Framework\TestCase;

final class SagaTest extends TestCase
{
    public function test_saga_executes_all_steps_on_success(): void
    {
        $workflow = new Workflow(new InMemorySagaStore(), new SagaExecutor(), new ValidateSagaTransition());
        $def = new SagaDefinition();

        $steps = 0;
        $def->step('step1', static function () use (&$steps): void {
            $steps++;
        });
        $def->step('step2', static function () use (&$steps): void {
            $steps++;
        });

        $workflow->runSaga($def);

        $this->assertEquals(2, $steps);
    }

    public function test_saga_runs_compensations_on_failure(): void
    {
        $workflow = new Workflow(new InMemorySagaStore(), new SagaExecutor(), new ValidateSagaTransition());
        $def = new SagaDefinition();

        $step1Done = false;
        $step1Compensated = false;

        $def->step(
            'step1',
            static function () use (&$step1Done): void {
                $step1Done = true;
            },
            static function () use (&$step1Compensated): void {
                $step1Compensated = true;
            },
        );

        $def->step('step2', static function (): void {
            throw new Exception('Fail at step 2');
        });

        try {
            $workflow->runSaga($def);
        } catch (Exception $e) {
            $this->assertEquals('Fail at step 2', $e->getMessage());
        }

        $this->assertTrue($step1Done);
        $this->assertTrue($step1Compensated);
    }
}
