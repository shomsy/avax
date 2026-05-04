<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ConfigureSagaRuntime;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga\InspectSaga;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ProtectSagaIdempotency\ProtectSagaIdempotency;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;
use stdClass;

final readonly class SagaRuntime
{
    public function __construct(
        public object                 $store,
        public object                 $stepRunner,
        public object                 $messageBus,
        public ProtectSagaIdempotency $idempotency,
        public InspectSaga            $inspect,
    )
    {
    }

    public static function inMemory(): self
    {
        return new self(
            store: StoreSagaState::inMemory(),
            stepRunner: new stdClass(),
            messageBus: new stdClass(),
            idempotency: ProtectSagaIdempotency::inMemory(),
            inspect: InspectSaga::inMemory(),
        );
    }
}
