<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\ApplicationWorkflow\Saga\ConfigureSagaRuntime;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ConfigureSagaRuntime\ConfigureSagaRuntime;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ConfigureSagaRuntime\SagaRuntimeConfig;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ConfigureSagaRuntime\SagaRuntimeConfigurationFailure;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;
use Avax\Tests\TestCase;

final class ConfigureSagaRuntimeTest extends TestCase
{
    public function test_missing_saga_store_fails_before_runtime(): void
    {
        $this->expectException(SagaRuntimeConfigurationFailure::class);
        $this->expectExceptionMessage('StoreSagaState');

        new ConfigureSagaRuntime()->validate(config: new SagaRuntimeConfig());
    }

    public function test_missing_step_runner_fails_before_runtime(): void
    {
        $runtime = new ConfigureSagaRuntime();
        $config  = $runtime->withStore(store: new StoreSagaState());

        $this->expectException(SagaRuntimeConfigurationFailure::class);
        $this->expectExceptionMessage('step runner');

        $runtime->validate(config: $config);
    }

    public function test_valid_runtime_config_keeps_explicit_dependencies(): void
    {
        $runtime    = new ConfigureSagaRuntime();
        $store      = new StoreSagaState();
        $stepRunner = static fn (): string => 'ran';

        $config = $runtime->withStepRunner(
            stepRunner: $stepRunner,
            config    : $runtime->withStore(store: $store),
        );

        $validated = $runtime->validate(config: $config);

        $this->assertSame($store, $validated->store);
        $this->assertSame($stepRunner, $validated->stepRunner);
    }
}
