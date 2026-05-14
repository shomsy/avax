<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

/**
 * Immutable result of saga definition validation.
 */
final readonly class SagaValidationResult
{
    /**
     * @param  list<string>  $errors
     * @param  list<string>  $warnings
     */
    public function __construct(
        public array $errors = [],
        public array $warnings = [],
    ) {
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }
}
