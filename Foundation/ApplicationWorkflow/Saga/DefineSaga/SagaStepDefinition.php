<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\DefineSaga;

use InvalidArgumentException;

enum SagaStepKind: string
{
    case ACTION       = 'action';
    case COMPENSATION = 'compensation';
    case APPROVAL     = 'approval';
    case NOTIFICATION = 'notification';
}

enum SagaStepRetryPolicy: string
{
    case NONE        = 'none';
    case IMMEDIATE   = 'immediate';
    case EXPONENTIAL = 'exponential';
    case LINEAR      = 'linear';
}

final readonly class SagaStepDefinition
{
    public string              $name;
    public string              $component;
    public SagaStepKind        $kind;
    public array       $input;
    public string|null $compensationComponent;
    public array|null  $compensationInput;
    public int         $maxRetries;
    public int                 $retryDelayMs;
    public SagaStepRetryPolicy $retryPolicy;
    public int                 $timeoutSeconds;
    public bool        $optional;
    public string|null $description;

    private function __construct(
        string                   $name,
        string                   $component,
        SagaStepKind             $kind,
        array|null               $input = null,
        string|null $compensationComponent = null,
        array|null  $compensationInput = null,
        int|null                 $maxRetries = null,
        int|null                 $retryDelayMs = null,
        SagaStepRetryPolicy|null $retryPolicy = null,
        int|null                 $timeoutSeconds = null,
        bool|null                $optional = null,
        string|null $description = null
    )
    {
        $input          ??= [];
        $maxRetries     ??= 0;
        $retryDelayMs   ??= 1000;
        $retryPolicy    ??= SagaStepRetryPolicy::NONE;
        $timeoutSeconds ??= 30;
        $optional       ??= false;
        $this->name                  = $name;
        $this->component             = $component;
        $this->kind                  = $kind;
        $this->input                 = $input;
        $this->compensationComponent = $compensationComponent;
        $this->compensationInput     = $compensationInput;
        $this->maxRetries            = $maxRetries;
        $this->retryDelayMs          = $retryDelayMs;
        $this->retryPolicy           = $retryPolicy;
        $this->timeoutSeconds        = $timeoutSeconds;
        $this->optional              = $optional;
        $this->description           = $description;
    }

    public static function create(
        string $name,
        string $component,
        array  $options = []
    ) : self
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException(message: 'Step name cannot be empty.');
        }

        if (empty(trim($component))) {
            throw new InvalidArgumentException(message: 'Component cannot be empty.');
        }

        $kind = SagaStepKind::from(value: $options['kind'] ?? 'action');

        return new self(
            name                 : $name,
            component            : $component,
            kind                 : $kind,
            input                : $options['input'] ?? [],
            compensationComponent: $options['compensation'] ?? null,
            compensationInput    : $options['compensation_input'] ?? null,
            maxRetries           : $options['max_retries'] ?? 0,
            retryDelayMs         : $options['retry_delay'] ?? 1000,
            retryPolicy          : SagaStepRetryPolicy::from(value: $options['retry_policy'] ?? 'none'),
            timeoutSeconds       : $options['timeout'] ?? 30,
            optional             : $options['optional'] ?? false,
            description          : $options['description'] ?? null
        );
    }

    public static function action(
        string     $name,
        string     $component,
        array|null $input = null,
        array      $options = []
    ) : self
    {
        $input ??= [];

        return self::create(name: $name, component: $component, options: array_merge($options, ['input' => $input, 'kind' => 'action']));
    }

    public static function withCompensation(
        string $name,
        string $component,
        array  $input,
        string $compensationComponent,
        array  $compensationInput,
        array  $options = []
    ) : self
    {
        return self::create(name: $name, component: $component, options: array_merge($options, [
            'input'              => $input,
            'compensation'       => $compensationComponent,
            'compensation_input' => $compensationInput,
        ]));
    }

    public function hasCompensation() : bool
    {
        return $this->compensationComponent !== null;
    }

    public function canRetry() : bool
    {
        return $this->maxRetries > 0 && $this->retryPolicy !== SagaStepRetryPolicy::NONE;
    }

    public function toArray() : array
    {
        return [
            'name'               => $this->name,
            'component'          => $this->component,
            'kind'               => $this->kind->value,
            'input'              => $this->input,
            'compensation'       => $this->compensationComponent,
            'compensation_input' => $this->compensationInput,
            'max_retries'        => $this->maxRetries,
            'retry_delay'        => $this->retryDelayMs,
            'retry_policy'       => $this->retryPolicy->value,
            'timeout'            => $this->timeoutSeconds,
            'optional'           => $this->optional,
            'description'        => $this->description,
        ];
    }
}