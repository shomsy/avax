<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\ConfigureSagaRuntime;

final readonly class RegisterSagaMessageBus
{
    /**
     * @param array<string, mixed> $config
     */
    public function register(string $type, array $config) : object
    {
        return match ($type) {
            'memory' => $this->createInMemoryBus(),
            'async'  => $this->createAsyncBus(config: $config),
            default  => throw new SagaRuntimeConfigurationFailure(
                message: sprintf('Unknown message bus type: %s', $type),
            ),
        };
    }

    private function createInMemoryBus() : object
    {
        return new class () {
            /** @var array<string, list<array<string, mixed>>> */
            public array $published = [];

            /**
             * @param array<string, mixed> $message
             */
            public function publish(string $topic, array $message) : void
            {
                $this->published[$topic][] = $message;
            }

            public function subscribe(string $topic, callable $handler) : void {}
        };
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createAsyncBus(array $config) : object
    {
        return new class ($config) {
            /** @var array<string, mixed> */
            private array $config;

            /**
             * @param array<string, mixed> $config
             */
            public function __construct(array $config)
            {
                $this->config = $config;
            }

            /**
             * @param array<string, mixed> $message
             */
            public function publish(string $topic, array $message) : void {}

            public function subscribe(string $topic, callable $handler) : void {}
        };
    }
}
