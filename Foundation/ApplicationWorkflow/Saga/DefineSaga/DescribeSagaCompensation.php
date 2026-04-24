<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\DefineSaga;

final readonly class DescribeSagaCompensation
{
    public function __construct(
        public string $stepName,
        public string $description,
        public array  $rollbackSchema,
        public bool   $idempotent
    ) {}

    public function describeResponsibility() : string
    {
        return 'describes saga compensation including step name, description, rollback, and idempotency.';
    }

    public static function create(
        string $stepName,
        string $description,
        bool   $idempotent = true
    ) : self
    {
        return new self(
            stepName      : $stepName,
            description   : $description,
            rollbackSchema: [],
            idempotent    : $idempotent
        );
    }

    public function toMetadata() : array
    {
        return [
            'step_name'       => $this->stepName,
            'description'     => $this->description,
            'rollback_schema' => $this->rollbackSchema,
            'idempotent'      => $this->idempotent,
        ];
    }
}