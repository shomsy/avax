<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\ConfigureSagaRuntime;

use Avax\ApplicationWorkflow\Saga\InspectSaga\InspectSaga;
use Avax\ApplicationWorkflow\Saga\ProtectSagaIdempotency\ProtectSagaIdempotency;
use Avax\ApplicationWorkflow\Saga\StoreSagaState\StoreSagaState;
use stdClass;

final readonly class ConfigureSagaRuntime
{
    public function __construct(
        private RegisterSagaStore      $registerSagaStore = new RegisterSagaStore(),
        private RegisterSagaStepRunner $registerSagaStepRunner = new RegisterSagaStepRunner(),
        private RegisterSagaMessageBus $registerSagaMessageBus = new RegisterSagaMessageBus()
    ) {}

    public function configure(SagaRuntimeConfig $config) : SagaRuntime
    {
        $validation = new ValidateSagaRuntimeConfig(config: $config);
        if (! $validation->isValid()) {
            $errors = implode(', ', $validation->getErrors());
            throw new SagaRuntimeConfigurationFailure(
                message: sprintf('Invalid saga runtime configuration: %s', $errors)
            );
        }

        $store      = $this->registerSagaStore->register(type: $config->storeType, config: $config->storeConfig);
        $stepRunner = $this->registerSagaStepRunner->register();
        $messageBus = $this->registerSagaMessageBus->register($config->messageBusType, config: $config->messageBusConfig);

        return new SagaRuntime(
            store      : $store,
            stepRunner : $stepRunner,
            messageBus : $messageBus,
            idempotency: new ProtectSagaIdempotency(),
            inspect    : InspectSaga::inMemory()
        );
    }
}

final readonly class SagaRuntime
{
    public function __construct(
        public StoreSagaState         $store,
        public object                 $stepRunner,
        public object                 $messageBus,
        public ProtectSagaIdempotency $idempotency,
        public InspectSaga            $inspect
    ) {}

    public static function inMemory() : self
    {
        return new self(
            store      : StoreSagaState::inMemory(),
            stepRunner : new stdClass(),
            messageBus : new stdClass(),
            idempotency: ProtectSagaIdempotency::inMemory(),
            inspect    : InspectSaga::inMemory()
        );
    }
}

final readonly class SagaRuntimeConfig
{
    public string   $storeType;
    public array    $storeConfig;
    public string   $messageBusType;
    public array    $messageBusConfig;
    public int|null $timeoutSeconds;
    public int|null $maxRetries;

    private function __construct(
        string   $storeType,
        array    $storeConfig,
        string   $messageBusType,
        array    $messageBusConfig,
        int|null $timeoutSeconds,
        int|null $maxRetries
    )
    {
        $this->storeType        = $storeType;
        $this->storeConfig      = $storeConfig;
        $this->messageBusType   = $messageBusType;
        $this->messageBusConfig = $messageBusConfig;
        $this->timeoutSeconds   = $timeoutSeconds;
        $this->maxRetries       = $maxRetries;
    }

    public static function inMemory() : self
    {
        return new self(
            storeType       : 'memory',
            storeConfig     : [],
            messageBusType  : 'memory',
            messageBusConfig: [],
            timeoutSeconds  : 3600,
            maxRetries      : 3
        );
    }

    public static function create(array $config) : self
    {
        return new self(
            storeType       : $config['store_type'] ?? 'memory',
            storeConfig     : $config['store_config'] ?? [],
            messageBusType  : $config['message_bus_type'] ?? 'memory',
            messageBusConfig: $config['message_bus_config'] ?? [],
            timeoutSeconds  : $config['timeout_seconds'] ?? 3600,
            maxRetries      : $config['max_retries'] ?? 3
        );
    }
}