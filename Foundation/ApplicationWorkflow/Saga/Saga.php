<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga;

use Avax\ApplicationWorkflow\Saga\CompensateSaga\CompensateSaga;
use Avax\ApplicationWorkflow\Saga\CompleteSaga\CompleteSaga;
use Avax\ApplicationWorkflow\Saga\DefineSaga\DefineSaga;
use Avax\ApplicationWorkflow\Saga\InspectSaga\InspectSaga;
use Avax\ApplicationWorkflow\Saga\ProtectSagaIdempotency\ProtectSagaIdempotency;
use Avax\ApplicationWorkflow\Saga\ResumeSaga\ResumeSaga;
use Avax\ApplicationWorkflow\Saga\RunSagaStep\RunSagaStep;
use Avax\ApplicationWorkflow\Saga\StartSaga\StartSaga;
use Avax\ApplicationWorkflow\Saga\StoreSagaState\StoreSagaState;

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
