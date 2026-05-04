<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ConfigureSagaRuntime;

final readonly class ValidateSagaRuntimeConfig
{
    public function __construct(
        private SagaRuntimeConfig $sagaRuntimeConfig,
        private array $errors = [],
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->sagaRuntimeConfig->storeType === '' || $this->sagaRuntimeConfig->storeType === '0') {
            $this->errors[] = 'Store type is required.';
        }

        if ($this->sagaRuntimeConfig->timeoutSeconds !== null && $this->sagaRuntimeConfig->timeoutSeconds <= 0) {
            $this->errors[] = 'Timeout must be positive.';
        }

        if ($this->sagaRuntimeConfig->maxRetries !== null && $this->sagaRuntimeConfig->maxRetries < 0) {
            $this->errors[] = 'Max retries must be non-negative.';
        }
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
