<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ConfigureSagaRuntime;

use RuntimeException;

final readonly class RegisterSagaStore
{
    public function register(string $type, array $config): object
    {
        return match ($type) {
            'memory' => $this->createInMemoryStore(),
            'database' => $this->createDatabaseStore(config: $config),
            'redis'  => $this->createRedisStore(config: $config),
            default  => throw new SagaRuntimeConfigurationFailure(
                message: sprintf('Unknown saga store type: %s', $type),
            ),
        };
    }

    private function createInMemoryStore() : object
    {
        return new class () {
            public array $data = [];

            public function get(string $key): ?array
            {
                return $this->data[$key] ?? null;
            }

            public function set(string $key, array $value): void
            {
                $this->data[$key] = $value;
            }

            public function delete(string $key): void
            {
                unset($this->data[$key]);
            }

            public function all(): array
            {
                return $this->data;
            }
        };
    }

    private function createDatabaseStore(array $config): object
    {
        return new class ($config) {
            public function get(string $key): ?array
            {
                return null;
            }

            public function set(string $key, array $value) : void {}

            public function delete(string $key) : void {}

            public function all(): array
            {
                return [];
            }
        };
    }

    private function createRedisStore(array $config): object
    {
        return new class ($config) {
            public function get(string $key): ?array
            {
                return null;
            }

            public function set(string $key, array $value) : void {}

            public function delete(string $key) : void {}

            public function all(): array
            {
                return [];
            }
        };
    }
}

final readonly class RegisterSagaStepRunner
{
    public function register(): object
    {
        return new class () {
            public function run(array $stepDefinition, array $sagaData): mixed
            {
                throw new RuntimeException(message: 'Step runner not configured.');
            }

            public function compensate(array $compensationDefinition, array $previousResult): mixed
            {
                throw new RuntimeException(message: 'Compensation runner not configured.');
            }
        };
    }
}

final readonly class RegisterSagaMessageBus
{
    public function register(string $type, array $config): object
    {
        return match ($type) {
            'memory' => $this->createInMemoryBus(),
            'async' => $this->createAsyncBus(config: $config),
            default => throw new SagaRuntimeConfigurationFailure(
                message: sprintf('Unknown message bus type: %s', $type),
            ),
        };
    }

    private function createInMemoryBus() : object
    {
        return new class () {
            public array $published = [];

            public function publish(string $topic, array $message): void
            {
                $this->published[$topic][] = $message;
            }

            public function subscribe(string $topic, callable $handler) : void {}
        };
    }

    private function createAsyncBus(array $config): object
    {
        return new class ($config) {
            public function publish(string $topic, array $message) : void {}

            public function subscribe(string $topic, callable $handler) : void {}
        };
    }
}

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

final class SagaRuntimeConfigurationFailure extends RuntimeException
{
}
