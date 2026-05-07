<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

use InvalidArgumentException;

final readonly class SagaStepDefinition
{
    public array $input;

    public int $maxRetries;

    public int $retryDelayMs;

    public SagaStepRetryPolicy $retryPolicy;

    public int $timeoutSeconds;

    public bool $optional;

    private function __construct(
        public string        $name,
        public string        $component,
        public SagaStepKind  $kind,
        ?array               $input = null,
        public ?string       $compensationComponent = null,
        public ?array        $compensationInput = null,
        ?int                 $maxRetries = null,
        ?int                 $retryDelayMs = null,
        ?SagaStepRetryPolicy $sagaStepRetryPolicy = null,
        ?int                 $timeoutSeconds = null,
        ?bool                $optional = null,
        public ?string       $description = null,
    )
    {
        $input                ??= [];
        $maxRetries           ??= 0;
        $retryDelayMs         ??= 1000;
        $sagaStepRetryPolicy  ??= SagaStepRetryPolicy::NONE;
        $timeoutSeconds       ??= 30;
        $optional             ??= false;
        $this->input          = $input;
        $this->maxRetries     = $maxRetries;
        $this->retryDelayMs   = $retryDelayMs;
        $this->retryPolicy    = $sagaStepRetryPolicy;
        $this->timeoutSeconds = $timeoutSeconds;
        $this->optional       = $optional;
    }

    public static function action(
        string $name,
        string $component,
        ?array $input = null,
        array  $options = [],
    ) : self
    {
        $input ??= [];

        return self::create(name: $name, component: $component, options: array_merge($options, ['input' => $input, 'kind' => 'action']));
    }

    public static function create(
        string $name,
        string $component,
        array  $options = [],
    ) : self
    {
        if (in_array(trim($name), ['', '0'], true)) {
            throw new InvalidArgumentException(message: 'Step name cannot be empty.');
        }

        if (in_array(trim($component), ['', '0'], true)) {
            throw new InvalidArgumentException(message: 'Component cannot be empty.');
        }

        $sagaStepKind = SagaStepKind::from(value: $options['kind'] ?? 'action');

        return new self(
            name                 : $name,
            component            : $component,
            kind                 : $sagaStepKind,
            input                : $options['input'] ?? [],
            compensationComponent: $options['compensation'] ?? null,
            compensationInput    : $options['compensation_input'] ?? null,
            maxRetries           : $options['max_retries'] ?? 0,
            retryDelayMs         : $options['retry_delay'] ?? 1000,
            timeoutSeconds       : $options['timeout'] ?? 30,
            optional             : $options['optional'] ?? false,
            description          : $options['description'] ?? null,
            retryPolicy          : SagaStepRetryPolicy::from(value: $options['retry_policy'] ?? 'none'),
        );
    }

    public static function withCompensation(
        string $name,
        string $component,
        array  $input,
        string $compensationComponent,
        array  $compensationInput,
        array  $options = [],
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
