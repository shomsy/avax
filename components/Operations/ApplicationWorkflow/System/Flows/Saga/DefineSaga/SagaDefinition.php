<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

final readonly class SagaDefinition implements IteratorAggregate
{
    public array $steps;

    public array $stepOrder;

    public SagaStartCondition $startCondition;

    public int $timeoutSeconds;

    public int $maxDurationSeconds;

    public bool $allowConcurrent;

    public bool $allowRemoteCompensation;

    public SagaStatus $status;

    private function __construct(
        public string             $name,
        public string                 $type, array|null $steps = null, array|null $stepOrder = null,
        public TenantBoundary|null    $tenantBoundary = null, SagaStartCondition|null $sagaStartCondition = null,
        public string|null            $startTrigger = null, int|null $timeoutSeconds = null, int|null $maxDurationSeconds = null, bool|null $allowConcurrent = null, bool|null $allowRemoteCompensation = null,
        public string|null            $description = null, SagaStatus|null $sagaStatus = null,
        public DateTimeImmutable|null $createdAt = null,
        public DateTimeImmutable|null $updatedAt = null,
    )
    {
        $steps                         ??= [];
        $stepOrder                     ??= [];
        $sagaStartCondition            ??= SagaStartCondition::MANUAL;
        $timeoutSeconds                ??= 3600;
        $maxDurationSeconds            ??= 86400;
        $allowConcurrent               ??= false;
        $allowRemoteCompensation       ??= false;
        $sagaStatus                    ??= SagaStatus::DRAFT;
        $this->steps                   = $steps;
        $this->stepOrder               = $stepOrder;
        $this->startCondition          = $sagaStartCondition;
        $this->timeoutSeconds          = $timeoutSeconds;
        $this->maxDurationSeconds      = $maxDurationSeconds;
        $this->allowConcurrent         = $allowConcurrent;
        $this->allowRemoteCompensation = $allowRemoteCompensation;
        $this->status                  = $sagaStatus;
    }

    public static function linear(string $name, array $options = []) : self
    {
        return self::create(name: $name, type: 'linear', options: $options);
    }

    public static function create(
        string $name, string|null $type = null,
        array   $options = [],
    ) : self
    {
        $type ??= 'long_running_process';
        if (in_array(trim($name), ['', '0'], true)) {
            throw new InvalidArgumentException(message: 'Saga name cannot be empty.');
        }

        if (in_array(trim($type), ['', '0'], true)) {
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
            startTrigger           : $options['start_trigger'] ?? null,
            timeoutSeconds         : $options['timeout'] ?? 3600,
            maxDurationSeconds     : $options['max_duration'] ?? 86400,
            allowConcurrent        : $options['concurrent'] ?? false,
            allowRemoteCompensation: $options['remote_compensation'] ?? false,
            description            : $options['description'] ?? null,
            createdAt              : new DateTimeImmutable(),
            updatedAt              : new DateTimeImmutable(),
            startCondition         : SagaStartCondition::from(value: $options['start_condition'] ?? 'manual'),
            status                 : SagaStatus::DRAFT,
        );
    }

    public static function stateMachine(string $name, array $options = []) : self
    {
        return self::create(name: $name, type: 'state_machine', options: $options);
    }

    public function withSteps(SagaStepDefinition ...$steps) : self
    {
        $result = $this;
        foreach ($steps as $step) {
            $result = $result->withStep(step: $step);
        }

        return $result;
    }

    public function withStep(SagaStepDefinition $sagaStepDefinition) : self
    {
        $steps                            = $this->steps;
        $steps[$sagaStepDefinition->name] = $sagaStepDefinition;

        $stepOrder = $this->stepOrder;
        if (! in_array($sagaStepDefinition->name, $stepOrder, true)) {
            $stepOrder[] = $sagaStepDefinition->name;
        }

        return new self(
            name                   : $this->name,
            type                   : $this->type,
            steps                  : $steps,
            stepOrder              : $stepOrder,
            tenantBoundary         : $this->tenantBoundary,
            startTrigger           : $this->startTrigger,
            timeoutSeconds         : $this->timeoutSeconds,
            maxDurationSeconds     : $this->maxDurationSeconds,
            allowConcurrent        : $this->allowConcurrent,
            allowRemoteCompensation: $this->allowRemoteCompensation,
            description            : $this->description,
            createdAt              : $this->createdAt,
            updatedAt              : new DateTimeImmutable(),
            startCondition         : $this->startCondition,
            status                 : SagaStatus::DRAFT,
        );
    }

    public function isValid() : bool
    {
        return $this->validate()->isValid();
    }

    public function validate() : SagaValidationResult
    {
        return (new ValidateSagaDefinition())->validate(sagaDefinition: $this);
    }

    public function getValidationErrors() : array
    {
        return $this->validate()->errors;
    }

    public function getStep(string $name) : ?SagaStepDefinition
    {
        return $this->steps[$name] ?? null;
    }

    public function getFirstStep() : ?SagaStepDefinition
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
            'created_at'          => $this->createdAt?->format(format: DateTimeInterface::ISO8601),
            'updated_at'          => $this->updatedAt?->format(format: DateTimeInterface::ISO8601),
        ];
    }
}
