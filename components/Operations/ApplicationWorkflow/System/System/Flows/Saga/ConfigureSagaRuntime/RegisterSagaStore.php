<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\ConfigureSagaRuntime;

final readonly class RegisterSagaStore
{
    /**
     * @param array<string, mixed> $config
     */
    public function register(string $type, array $config) : object
    {
        return match ($type) {
            'memory'   => $this->createInMemoryStore(),
            'database' => $this->createDatabaseStore(config: $config),
            'redis'    => $this->createRedisStore(config: $config),
            default    => throw new SagaRuntimeConfigurationFailure(
                message: sprintf('Unknown saga store type: %s', $type),
            ),
        };
    }

    private function createInMemoryStore() : object
    {
        return new class () {
            /** @var array<string, array<string, mixed>> */
            public array $data = [];

            /**
             * @return array<string, mixed>|null
             */
            public function get(string $key) : array|null
            {
                return $this->data[$key] ?? null;
            }

            /**
             * @param array<string, mixed> $value
             */
            public function set(string $key, array $value) : void
            {
                $this->data[$key] = $value;
            }

            public function delete(string $key) : void
            {
                unset($this->data[$key]);
            }

            /**
             * @return array<string, array<string, mixed>>
             */
            public function all() : array
            {
                return $this->data;
            }
        };
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createDatabaseStore(array $config) : object
    {
        return new class ($config) {
            /** @var array<string, mixed> */
            private array $config;

            /** @var array<string, array<string, mixed>> */
            private array $data = [];

            /**
             * @param array<string, mixed> $config
             */
            public function __construct(array $config)
            {
                $this->config = $config;
            }

            /**
             * @return array<string, mixed>|null
             */
            public function get(string $key) : array|null
            {
                return $this->data[$key] ?? null;
            }

            /**
             * @param array<string, mixed> $value
             */
            public function set(string $key, array $value) : void
            {
                $this->data[$key] = $value;
            }

            public function delete(string $key) : void
            {
                unset($this->data[$key]);
            }

            /**
             * @return array<string, array<string, mixed>>
             */
            public function all() : array
            {
                return $this->data;
            }
        };
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createRedisStore(array $config) : object
    {
        return new class ($config) {
            /** @var array<string, mixed> */
            private array $config;

            /** @var array<string, array<string, mixed>> */
            private array $data = [];

            /**
             * @param array<string, mixed> $config
             */
            public function __construct(array $config)
            {
                $this->config = $config;
            }

            /**
             * @return array<string, mixed>|null
             */
            public function get(string $key) : array|null
            {
                return $this->data[$key] ?? null;
            }

            /**
             * @param array<string, mixed> $value
             */
            public function set(string $key, array $value) : void
            {
                $this->data[$key] = $value;
            }

            public function delete(string $key) : void
            {
                unset($this->data[$key]);
            }

            /**
             * @return array<string, array<string, mixed>>
             */
            public function all() : array
            {
                return $this->data;
            }
        };
    }
}
