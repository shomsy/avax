<?php

declare(strict_types=1);

namespace Avax\DataLayer\DescribeStorageBehavior;

final readonly class DescribeRecoveryPath
{
    public string $recoveryType;
    public string $restoreProcedure;
    public array  $recoverySteps;

    public function __construct(
        string $recoveryType,
        string $restoreProcedure,
        array  $recoverySteps = []
    )
    {
        $this->recoveryType     = $recoveryType;
        $this->restoreProcedure = $restoreProcedure;
        $this->recoverySteps    = $recoverySteps;
    }

    public function describeResponsibility() : string
    {
        return 'describes recovery path including procedure, type, and recovery steps.';
    }

    public function addRecoveryStep(string $step) : self
    {
        return new self(
            recoveryType    : $this->recoveryType,
            restoreProcedure: $this->restoreProcedure,
            recoverySteps   : [...$this->recoverySteps, $step]
        );
    }

    public function toMetadata() : array
    {
        return [
            'recovery_type'     => $this->recoveryType,
            'restore_procedure' => $this->restoreProcedure,
            'recovery_steps'    => $this->recoverySteps,
        ];
    }
}