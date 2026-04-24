<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\DefineSaga;

use Closure;

/**
 * SagaCompensationDefinition - names the business action that neutralizes a completed side-effect step.
 */
final readonly class SagaCompensationDefinition
{
    public function __construct(
        public string       $name,
        public Closure|null $runCompensation = null
    ) {}
}
