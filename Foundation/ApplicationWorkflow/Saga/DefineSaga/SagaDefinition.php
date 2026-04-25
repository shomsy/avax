<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\DefineSaga;

use Avax\DataLayer\ProtectStoredData\TenantBoundary;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

enum SagaStatus: string
{
    case DRAFT      = 'draft';
    case DEFINED    = 'defined';
    case VALID      = 'valid';
    case INVALID    = 'invalid';
    case REGISTERED = 'registered';
}

enum SagaStartCondition: string
{
    case MANUAL   = 'manual';
    case EVENT    = 'event';
    case SCHEDULE = 'schedule';
    case COMMAND  = 'command';
}

final readonly class SagaDefinition implements IteratorAggregate
{
    public string              $name;
    public string              $type;
    public array               $steps;
    public array                  $stepOrder;
    public TenantBoundary|null    $tenantBoundary;
    public SagaStartCondition     $startCondition;
    public string|null            $startTrigger;
    public int                    $timeoutSeconds;
    public int                 $maxDurationSeconds;
    public bool                $allowConcurrent;
    public bool                   $allowRemoteCompensation;
    public string|null            $description;
    public SagaStatus             $status;
    public DateTimeImmutable|null $createdAt;
    public DateTimeImmutable|null $updatedAt;

    private function __construct(
        string                  $name,
        string                  $type,
        array|null              $steps = null,
        array|null              $stepOrder = null,
        TenantBoundary|null    $tenantBoundary = null,
        SagaStartCondition|null $startCondition = null,
        string|null            $startTrigger = null,
        int|null                $timeoutSeconds = null,
        int|null                $maxDurationSeconds = null,
        bool|null               $allowConcurrent = null,
        bool|null               $allowRemoteCompensation = null,
        string|null            $description = null,
        SagaStatus|null         $status = null,
        DateTimeImmutable|null $createdAt = null,
        DateTimeImmutable|null $updatedAt = null
    )
    {
        $steps                   ??= [];
        $stepOrder               ??= [];
        $startCondition          ??= SagaStartCondition::MANUAL;
        $timeoutSeconds          ??= 3600;
        $maxDurationSeconds      ??= 86400;
        $allowConcurrent         ??= false;
        $allowRemoteCompensation ??= false;
        $status                  ??= SagaStatus::DRAFT;
        $this->name                    = $name;
        $this->type                    = $type;
        $this->steps                   = $steps;
        $this->stepOrder               = $stepOrder;
        $this->tenantBoundary          = $tenantBoundary;
        $this->startCondition          = $startCondition;
        $this->startTrigger            = $startTrigger;
        $this->timeoutSeconds          = $timeoutSeconds;
        $this->maxDurationSeconds      = $maxDurationSeconds;
        $this->allowConcurrent         = $allowConcurrent;
        $this->allowRemoteCompensation = $allowRemoteCompensation;
        $this->description             = $description;
        $this->status                  = $status;
        $this->createdAt               = $createdAt;
        $this->updatedAt               = $updatedAt;
    }

    public static function create(
        string      $name,
        string|null $type = null,
        array       $options = []
    ) : self
    {
        $type ??= 'long_running_process';
        if (empty(trim($name))) {
            throw new InvalidArgumentException(message: 'Saga name cannot be empty.');
        }

        if (empty(trim($type))) {
            throw new InvalidArgumentException(message: 'Saga type cannot be empty.');
        }

        return new self(
            name                   : $name,
            type                   : $type,
            steps                  : [],
            stepOrder              : [],
            tenantBoundary         : isset($options['tenant'])
                                         ? TenantBoundary::create(id: $options['tenant'])
                                         : null,
            startCondition         : SagaStartCondition::from(value: $options['start_condition'] ?? 'manual'),
            startTrigger           : $options['start_trigger'] ?? null,
            timeoutSeconds         : $options['timeout'] ?? 3600,
            maxDurationSeconds     : $options['max_duration'] ?? 86400,
            allowConcurrent        : $options['concurrent'] ?? false,
            allowRemoteCompensation: $options['remote_compensation'] ?? false,
            description            : $options['description'] ?? null,
            status                 : SagaStatus::DRAFT,
            createdAt              : new DateTimeImmutable(),
            updatedAt              : new DateTimeImmutable()
        );
    }

    public static function linear(string $name, array $options = []) : self
    {
        return self::create(name: $name, type: 'linear', options: $options);
    }

    public static function stateMachine(string $name, array $options = []) : self
    {
        return self::create(name: $name, type: 'state_machine', options: $options);
    }

    public function withStep(SagaStepDefinition $step) : self
    {
        $steps              = $this->steps;
        $steps[$step->name] = $step;

        $stepOrder = $this->stepOrder;
        if (! in_array($step->name, $stepOrder, true)) {
            $stepOrder[] = $step->name;
        }

        return new self(
            name                   : $this->name,
            type                   : $this->type,
            steps                  : $steps,
            stepOrder              : $stepOrder,
            tenantBoundary         : $this->tenantBoundary,
            startCondition         : $this->startCondition,
            startTrigger           : $this->startTrigger,
            timeoutSeconds         : $this->timeoutSeconds,
            maxDurationSeconds     : $this->maxDurationSeconds,
            allowConcurrent        : $this->allowConcurrent,
            allowRemoteCompensation: $this->allowRemoteCompensation,
            description            : $this->description,
            status                 : SagaStatus::DRAFT,
            createdAt              : $this->createdAt,
            updatedAt              : new DateTimeImmutable()
        );
    }

    public function withSteps(SagaStepDefinition ...$steps) : self
    {
        $result = $this;
        foreach ($steps as $step) {
            $result = $result->withStep(step: $step);
        }

        return $result;
    }

    public function validate() : ValidateSagaDefinition
    {
        return new ValidateSagaDefinition(definition: $this);
    }

    public function isValid() : bool
    {
        return $this->validate()->isValid();
    }

    public function getValidationErrors() : array
    {
        return $this->validate()->getErrors();
    }

    public function getStep(string $name) : SagaStepDefinition|null
    {
        return $this->steps[$name] ?? null;
    }

    public function getFirstStep() : SagaStepDefinition|null
    {
        $name = $this->stepOrder[0] ?? null;

        return $name ? ($this->steps[$name] ?? null) : null;
    }

    public function stepCount() : int
    {
        return count($this->steps);
    }

    public function totalTimeout() : int
    {
        $total = 0;
        foreach ($this->steps as $step) {
            $total += $step->timeoutSeconds;
        }

        return min($total, $this->maxDurationSeconds);
    }

    public function getIterator() : Traversable
    {
        foreach ($this->stepOrder as $name) {
            yield $name => $this->steps[$name];
        }
    }

    public function toArray() : array
    {
        $steps = [];
        foreach ($this->stepOrder as $name) {
            $steps[] = $this->steps[$name]->toArray();
        }

        return [
            'name'                => $this->name,
            'type'                => $this->type,
            'steps'               => $steps,
            'tenant'              => $this->tenantBoundary?->toArray(),
            'start_condition'     => $this->startCondition->value,
            'start_trigger'       => $this->startTrigger,
            'timeout'             => $this->timeoutSeconds,
            'max_duration'        => $this->maxDurationSeconds,
            'concurrent'          => $this->allowConcurrent,
            'remote_compensation' => $this->allowRemoteCompensation,
            'description'         => $this->description,
            'status'              => $this->status->value,
            'created_at' => $this->createdAt?->format(format: DateTimeInterface::ISO8601),
            'updated_at' => $this->updatedAt?->format(format: DateTimeInterface::ISO8601),
        ];
    }
}