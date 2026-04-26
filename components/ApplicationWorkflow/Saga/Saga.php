<?php

declare(strict_types=1);

namespace components\ApplicationWorkflow\Saga;

use components\ApplicationWorkflow\Saga\CompensateSaga\CompensateSaga;
use components\ApplicationWorkflow\Saga\CompleteSaga\CompleteSaga;
use components\ApplicationWorkflow\Saga\DefineSaga\DefineSaga;
use components\ApplicationWorkflow\Saga\InspectSaga\InspectSaga;
use components\ApplicationWorkflow\Saga\ProtectSagaIdempotency\ProtectSagaIdempotency;
use components\ApplicationWorkflow\Saga\ResumeSaga\ResumeSaga;
use components\ApplicationWorkflow\Saga\RunSagaStep\RunSagaStep;
use components\ApplicationWorkflow\Saga\StartSaga\StartSaga;
use components\ApplicationWorkflow\Saga\StoreSagaState\StoreSagaState;

/**
 * Saga - small facade for long-running workflow state transitions, compensation, recovery, and idempotency.
 */
final readonly class Saga
{
    public function __construct(
        private DefineSaga             $defineSaga,
        private StartSaga              $startSaga,
        private RunSagaStep            $runSagaStep,
        private CompleteSaga           $completeSaga,
        private CompensateSaga         $compensateSaga,
        private ResumeSaga             $resumeSaga,
        private ProtectSagaIdempotency $protectSagaIdempotency,
        private StoreSagaState         $storeSagaState,
        private InspectSaga            $inspectSaga
    ) {}

    public static function inMemory() : self
    {
        $store       = new StoreSagaState();
        $idempotency = new ProtectSagaIdempotency();
        $inspect     = new InspectSaga();

        return new self(
            defineSaga            : new DefineSaga(),
            startSaga             : new StartSaga(storeSagaState: $store, protectSagaIdempotency: $idempotency, inspectSaga: $inspect),
            runSagaStep           : new RunSagaStep(storeSagaState: $store, inspectSaga: $inspect),
            completeSaga          : new CompleteSaga(storeSagaState: $store, inspectSaga: $inspect),
            compensateSaga        : new CompensateSaga(storeSagaState: $store, inspectSaga: $inspect),
            resumeSaga            : new ResumeSaga(storeSagaState: $store),
            protectSagaIdempotency: $idempotency,
            storeSagaState        : $store,
            inspectSaga           : $inspect
        );
    }

    public function define() : DefineSaga
    {
        return $this->defineSaga;
    }

    public function start() : StartSaga
    {
        return $this->startSaga;
    }

    public function runStep() : RunSagaStep
    {
        return $this->runSagaStep;
    }

    public function complete() : CompleteSaga
    {
        return $this->completeSaga;
    }

    public function compensate() : CompensateSaga
    {
        return $this->compensateSaga;
    }

    public function resume() : ResumeSaga
    {
        return $this->resumeSaga;
    }

    public function idempotency() : ProtectSagaIdempotency
    {
        return $this->protectSagaIdempotency;
    }

    public function state() : StoreSagaState
    {
        return $this->storeSagaState;
    }

    public function inspect() : InspectSaga
    {
        return $this->inspectSaga;
    }
}
