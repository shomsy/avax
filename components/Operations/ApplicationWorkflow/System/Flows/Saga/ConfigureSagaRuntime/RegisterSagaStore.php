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
