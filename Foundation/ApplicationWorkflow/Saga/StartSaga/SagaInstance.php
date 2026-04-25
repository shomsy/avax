<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\StartSaga;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

enum SagaInstanceStatus: string
{
    case PENDING      = 'pending';
    case RUNNING      = 'running';
    case COMPLETED    = 'completed';
    case COMPENSATING = 'compensating';
    case COMPENSATED  = 'compensated';
    case FAILED       = 'failed';
    case TIMEOUT      = 'timeout';
}

final readonly class SagaInstance
{
    public string              $id;
    public string              $definitionName;
    public string              $type;
    public SagaInstanceStatus  $status;
    public int                    $currentStepIndex;
    public string|null            $currentStepName;
    public array                  $data;
    public array               $completedSteps;
    public array                  $stepResults;
    public DateTimeImmutable|null $startedAt;
    public DateTimeImmutable|null $completedAt;
    public DateTimeImmutable|null $timeoutAt;
    public string|null            $correlationId;
    public string|null            $tenantId;

    private function __construct(
        string                 $id,
        string                 $definitionName,
        string                 $type,
        SagaInstanceStatus     $status,
        int                    $currentStepIndex,
        string|null            $currentStepName,
        array                  $data,
        array                  $completedSteps,
        array                  $stepResults,
        DateTimeImmutable|null $startedAt,
        DateTimeImmutable|null $completedAt,
        DateTimeImmutable|null $timeoutAt,
        string|null            $correlationId,
        string|null            $tenantId
    )
    {
        $this->id               = $id;
        $this->definitionName   = $definitionName;
        $this->type             = $type;
        $this->status           = $status;
        $this->currentStepIndex = $currentStepIndex;
        $this->currentStepName  = $currentStepName;
        $this->data             = $data;
        $this->completedSteps   = $completedSteps;
        $this->stepResults      = $stepResults;
        $this->startedAt        = $startedAt;
        $this->completedAt      = $completedAt;
        $this->timeoutAt        = $timeoutAt;
        $this->correlationId    = $correlationId;
        $this->tenantId         = $tenantId;
    }

    public static function create(
        string $id,
        string $definitionName,
        string $type,
        array  $initialData,
        array  $options = []
    ) : self
    {
        if (empty(trim($id))) {
            throw new InvalidArgumentException(message: 'Saga instance ID cannot be empty.');
        }

        $startedAt = new DateTimeImmutable();
        $timeoutAt = isset($options['timeout_seconds'])
            ? new DateTimeImmutable()->modify(modifier: sprintf('+%d seconds', $options['timeout_seconds']))
            : null;

        return new self(
            id              : $id,
            definitionName  : $definitionName,
            type            : $type,
            status          : SagaInstanceStatus::PENDING,
            currentStepIndex: 0,
            currentStepName : null,
            data            : $initialData,
            completedSteps  : [],
            stepResults     : [],
            startedAt       : $startedAt,
            completedAt     : null,
            timeoutAt       : $timeoutAt,
            correlationId   : $options['correlation_id'] ?? self::generateCorrelationId(),
            tenantId        : $options['tenant_id'] ?? null
        );
    }

    public static function fromArray(array $row) : self
    {
        return new self(
            id              : $row['id'],
            definitionName  : $row['definition_name'],
            type            : $row['type'],
            status          : SagaInstanceStatus::from(value: $row['status']),
            currentStepIndex: (int) ($row['current_step_index'] ?? 0),
            currentStepName : $row['current_step_name'] ?? null,
            data            : json_decode($row['data'] ?? '{}', true),
            completedSteps  : json_decode($row['completed_steps'] ?? '[]', true),
            stepResults     : json_decode($row['step_results'] ?? '{}', true),
            startedAt       : isset($row['started_at']) ? new DateTimeImmutable(datetime: $row['started_at']) : null,
            completedAt     : isset($row['completed_at']) ? new DateTimeImmutable(datetime: $row['completed_at']) : null,
            timeoutAt       : isset($row['timeout_at']) ? new DateTimeImmutable(datetime: $row['timeout_at']) : null,
            correlationId   : $row['correlation_id'] ?? null,
            tenantId        : $row['tenant_id'] ?? null
        );
    }

    public function start(string $firstStepName) : self
    {
        return new self(
            id              : $this->id,
            definitionName  : $this->definitionName,
            type            : $this->type,
            status          : SagaInstanceStatus::RUNNING,
            currentStepIndex: 0,
            currentStepName : $firstStepName,
            data            : $this->data,
            completedSteps  : $this->completedSteps,
            stepResults     : $this->stepResults,
            startedAt       : $this->startedAt,
            completedAt     : $this->completedAt,
            timeoutAt       : $this->timeoutAt,
            correlationId   : $this->correlationId,
            tenantId        : $this->tenantId
        );
    }

    public function advanceTo(string $stepName, int $stepIndex, array $result) : self
    {
        $completedSteps   = $this->completedSteps;
        $completedSteps[] = $this->currentStepName;

        $stepResults                               = $this->stepResults;
        $stepResults[$this->currentStepName ?? ''] = $result;

        return new self(
            id              : $this->id,
            definitionName  : $this->definitionName,
            type            : $this->type,
            status          : SagaInstanceStatus::RUNNING,
            currentStepIndex: $stepIndex,
            currentStepName : $stepName,
            data            : $this->data,
            completedSteps  : $completedSteps,
            stepResults     : $stepResults,
            startedAt       : $this->startedAt,
            completedAt     : $this->completedAt,
            timeoutAt       : $this->timeoutAt,
            correlationId   : $this->correlationId,
            tenantId        : $this->tenantId
        );
    }

    public function complete() : self
    {
        return new self(
            id              : $this->id,
            definitionName  : $this->definitionName,
            type            : $this->type,
            status          : SagaInstanceStatus::COMPLETED,
            currentStepIndex: $this->currentStepIndex,
            currentStepName : $this->currentStepName,
            data            : $this->data,
            completedSteps  : $this->completedSteps,
            stepResults     : $this->stepResults,
            startedAt       : $this->startedAt,
            completedAt     : new DateTimeImmutable(),
            timeoutAt       : $this->timeoutAt,
            correlationId   : $this->correlationId,
            tenantId        : $this->tenantId
        );
    }

    public function fail(string $error) : self
    {
        $stepResults                               = $this->stepResults;
        $stepResults[$this->currentStepName ?? ''] = ['error' => $error, 'failed_at' => new DateTimeImmutable()->format(format: DateTimeInterface::ISO8601)];

        return new self(
            id              : $this->id,
            definitionName  : $this->definitionName,
            type            : $this->type,
            status          : SagaInstanceStatus::FAILED,
            currentStepIndex: $this->currentStepIndex,
            currentStepName : $this->currentStepName,
            data            : $this->data,
            completedSteps  : $this->completedSteps,
            stepResults     : $stepResults,
            startedAt       : $this->startedAt,
            completedAt     : new DateTimeImmutable(),
            timeoutAt       : $this->timeoutAt,
            correlationId   : $this->correlationId,
            tenantId        : $this->tenantId
        );
    }

    public function compensate(array $compensationResults = []) : self
    {
        return new self(
            id              : $this->id,
            definitionName  : $this->definitionName,
            type            : $this->type,
            status          : SagaInstanceStatus::COMPENSATED,
            currentStepIndex: $this->currentStepIndex,
            currentStepName : $this->currentStepName,
            data            : $this->data,
            completedSteps  : $this->completedSteps,
            stepResults     : $compensationResults,
            startedAt       : $this->startedAt,
            completedAt     : new DateTimeImmutable(),
            timeoutAt       : $this->timeoutAt,
            correlationId   : $this->correlationId,
            tenantId        : $this->tenantId
        );
    }

    public function retry() : self
    {
        return new self(
            id              : $this->id,
            definitionName  : $this->definitionName,
            type            : $this->type,
            status          : SagaInstanceStatus::RUNNING,
            currentStepIndex: $this->currentStepIndex,
            currentStepName : $this->currentStepName,
            data            : $this->data,
            completedSteps  : $this->completedSteps,
            stepResults     : [],
            startedAt       : $this->startedAt,
            completedAt     : null,
            timeoutAt       : $this->timeoutAt,
            correlationId   : $this->correlationId,
            tenantId        : $this->tenantId
        );
    }

    public function toArray() : array
    {
        return [
            'id'                 => $this->id,
            'definition_name'    => $this->definitionName,
            'type'               => $this->type,
            'status'             => $this->status->value,
            'current_step_index' => $this->currentStepIndex,
            'current_step_name'  => $this->currentStepName,
            'data'               => $this->data,
            'completed_steps'    => $this->completedSteps,
            'step_results'       => $this->stepResults,
            'started_at'   => $this->startedAt?->format(format: DateTimeInterface::ISO8601),
            'completed_at' => $this->completedAt?->format(format: DateTimeInterface::ISO8601),
            'timeout_at'   => $this->timeoutAt?->format(format: DateTimeInterface::ISO8601),
            'correlation_id'     => $this->correlationId,
            'tenant_id'          => $this->tenantId,
        ];
    }

    private static function generateCorrelationId() : string
    {
        return sprintf('saga_%s_%s', date('YmdHis'), bin2hex(random_bytes(6)));
    }
}