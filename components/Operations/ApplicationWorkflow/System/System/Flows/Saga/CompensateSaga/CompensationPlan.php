<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\CompensateSaga;

final readonly class CompensationPlan
{
    public function __construct(
        public string $sagaType,
        public array  $steps,
        public bool   $parallel,
        public int    $timeoutMs,
    ) {}

    public static function create(string $sagaType, array $steps) : self
    {
        return new self(
            steps    : $steps,
            sagaType : $sagaType,
            parallel : false,
            timeoutMs: 30000,
        );
    }

    public function orderedSteps() : array
    {
        return array_reverse($this->steps);
    }

    public function toMetadata() : array
    {
        return [
            'saga_type'  => $this->sagaType,
            'step_count' => count($this->steps),
            'parallel'   => $this->parallel,
            'timeout_ms' => $this->timeoutMs,
        ];
    }
}
