<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ConfigureSagaRuntime;

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
