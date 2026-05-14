<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\RegisterSagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\ValidateSagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;
use Override;

/**
 * ApplicationWorkflowServiceProvider — registers workflow and saga dependencies.
 */
final readonly class ApplicationWorkflowServiceProvider implements ServiceProvider
{
    #[Override]
    public function register(ContainerInterface $container): void
    {
        $container->singleton(
            abstract: StoreSagaState::class,
            concrete: static fn (): StoreSagaState => new StoreSagaState(),
        );

        $container->singleton(
            abstract: ValidateSagaDefinition::class,
            concrete: static fn (): ValidateSagaDefinition => new ValidateSagaDefinition(),
        );

        $container->singleton(
            abstract: RegisterSagaDefinition::class,
            concrete: static fn (ContainerInterface $c): RegisterSagaDefinition => new RegisterSagaDefinition(
                validateSagaDefinition: $c->get(ValidateSagaDefinition::class),
            ),
        );
    }

    #[Override]
    public function boot(ContainerInterface $container): void
    {
        // No boot-time actions required.
    }
}
