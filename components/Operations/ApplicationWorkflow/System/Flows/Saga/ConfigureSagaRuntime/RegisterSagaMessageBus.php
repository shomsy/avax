<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ConfigureSagaRuntime;

use Closure;

/**
 * RegisterSagaMessageBus - registers the saga message bus dependency.
 */
final readonly class RegisterSagaMessageBus
{
    public function register(Closure $messageBus, SagaRuntimeConfig $config = null) : SagaRuntimeConfig
    {
        return ($config ?? new SagaRuntimeConfig())->withMessageBus(messageBus: $messageBus);
    }

    public function describeResponsibility() : string
    {
        return 'registers the saga message bus dependency.';
    }
}
