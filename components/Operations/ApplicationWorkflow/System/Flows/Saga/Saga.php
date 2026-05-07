<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\CompensateSaga\CompensateSaga;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\CompleteSaga\CompleteSaga;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\DefineSaga;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga\InspectSaga;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ProtectSagaIdempotency\ProtectSagaIdempotency;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga\ResumeSaga;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\RunSagaStep\RunSagaStep;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\StartSaga;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;

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
        private InspectSaga            $inspectSaga,
    ) {}

    public static function inMemory() : self
    {
        $storeSagaState         = new StoreSagaState();
        $protectSagaIdempotency = new ProtectSagaIdempotency();
        $inspectSaga            = new InspectSaga();

        return new self(
            defineSaga            : new DefineSaga(),
            startSaga             : new StartSaga(storeSagaState: $storeSagaState, protectSagaIdempotency: $protectSagaIdempotency, inspectSaga: $inspectSaga),
            runSagaStep           : new RunSagaStep(storeSagaState: $storeSagaState, inspectSaga: $inspectSaga),
            completeSaga          : new CompleteSaga(storeSagaState: $storeSagaState, inspectSaga: $inspectSaga),
            compensateSaga        : new CompensateSaga(storeSagaState: $storeSagaState, inspectSaga: $inspectSaga),
            resumeSaga            : new ResumeSaga(storeSagaState: $storeSagaState),
            protectSagaIdempotency: $protectSagaIdempotency,
            storeSagaState        : $storeSagaState,
            inspectSaga           : $inspectSaga,
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
