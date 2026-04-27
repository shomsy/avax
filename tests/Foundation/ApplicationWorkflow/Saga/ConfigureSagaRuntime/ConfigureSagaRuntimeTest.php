<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\ApplicationWorkflow\Saga\ConfigureSagaRuntime;

use Avax\ApplicationWorkflow\Saga\ConfigureSagaRuntime\ConfigureSagaRuntime;
use Avax\ApplicationWorkflow\Saga\ConfigureSagaRuntime\SagaRuntimeConfig;
use Avax\ApplicationWorkflow\Saga\ConfigureSagaRuntime\SagaRuntimeConfigurationFailure;
use Avax\ApplicationWorkflow\Saga\StoreSagaState\StoreSagaState;
use Avax\Tests\TestCase;

final class ConfigureSagaRuntimeTest extends TestCase
{
    public function testMissingSagaStoreFailsBeforeRuntime() : void
    {
        $this->expectException(SagaRuntimeConfigurationFailure::class);
        $this->expectExceptionMessage('StoreSagaState');

        new ConfigureSagaRuntime()->validate(config: new SagaRuntimeConfig());
    }

    public function testMissingStepRunnerFailsBeforeRuntime() : void
    {
        $runtime = new ConfigureSagaRuntime();
        $config  = $runtime->withStore(store: new StoreSagaState());

        $this->expectException(SagaRuntimeConfigurationFailure::class);
        $this->expectExceptionMessage('step runner');

        $runtime->validate(config: $config);
    }

    public function testValidRuntimeConfigKeepsExplicitDependencies() : void
    {
        $runtime    = new ConfigureSagaRuntime();
        $store      = new StoreSagaState();
        $stepRunner = static fn () : string => 'ran';

        $config = $runtime->withStepRunner(
            stepRunner: $stepRunner,
            config    : $runtime->withStore(store: $store)
        );

        $validated = $runtime->validate(config: $config);

        $this->assertSame($store, $validated->store);
        $this->assertSame($stepRunner, $validated->stepRunner);
    }
}
