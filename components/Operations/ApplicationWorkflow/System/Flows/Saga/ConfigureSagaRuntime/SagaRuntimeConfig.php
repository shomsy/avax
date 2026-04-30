<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ConfigureSagaRuntime;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;
use Closure;

/**
 * SagaRuntimeConfig - records saga store, step runner, message bus, and event recorder dependencies.
 */
final readonly class SagaRuntimeConfig
{
    public function __construct(
        public StoreSagaState|null $store = null,
        public Closure|null $stepRunner = null,
        public Closure|null $messageBus = null,
        public Closure|null $eventRecorder = null,
    ) {}

    public function withStore(StoreSagaState $store) : self
    {
        return new self(
            store        : $store,
            stepRunner   : $this->stepRunner,
            messageBus   : $this->messageBus,
            eventRecorder: $this->eventRecorder,
        );
    }

    public function withStepRunner(Closure $stepRunner) : self
    {
        return new self(
            store        : $this->store,
            stepRunner   : $stepRunner,
            messageBus   : $this->messageBus,
            eventRecorder: $this->eventRecorder,
        );
    }

    public function withMessageBus(Closure $messageBus) : self
    {
        return new self(
            store        : $this->store,
            stepRunner   : $this->stepRunner,
            messageBus   : $messageBus,
            eventRecorder: $this->eventRecorder,
        );
    }

    public function describeResponsibility() : string
    {
        return 'records saga store, step runner, message bus, and event recorder dependencies.';
    }
}
