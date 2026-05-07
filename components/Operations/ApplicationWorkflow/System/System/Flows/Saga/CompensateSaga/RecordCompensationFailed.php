<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\CompensateSaga;

final readonly class RecordCompensationFailed
{
    public function __construct(
        private string $sagaType,
    ) {}

    public function record(
        string $sagaId,
        string $stepName,
        string $error,
        array  $context = [],
    ) : RecordCompensationFailedResult
    {
        return new RecordCompensationFailedResult(
            sagaId    : $sagaId,
            stepName  : $stepName,
            error     : $error,
            context   : $context,
            recordedAt: microtime(true),
        );
    }

    public function toMetadata() : array
    {
        return ['saga_type' => $this->sagaType];
    }
}

final readonly class RecordCompensationFailedResult
{
    public function __construct(
        public string $sagaId,
        public string $stepName,
        public string $error,
        public array  $context,
        public float  $recordedAt,
    ) {}
}
