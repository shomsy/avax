<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Commands\App\MakeControllerCommand;
use Avax\Commands\App\MakeEntityCommand;
use Avax\Commands\App\MakeRepositoryCommand;
use Avax\Commands\App\MakeServiceCommand;
use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\Database\Migration\Design\Column\Column;
use Avax\Database\Migration\Design\Mapper\FieldToDslMapperInterface;
use Avax\Database\Migration\Design\Mapper\FluentFieldToDslMapper;
use Avax\Database\Migration\Runner\Commands\{ValidateStubsCommand};
use Avax\Database\Migration\Runner\Commands\MakeMigrationCommand;
use Avax\Database\Migration\Runner\Generators\{Repository\RepositoryGenerator};
use Avax\Database\Migration\Runner\Generators\Controller\ControllerGenerator;
use Avax\Database\Migration\Runner\Generators\DTO\DtoGenerator;
use Avax\Database\Migration\Runner\Generators\Entity\EntityGenerator;
use Avax\Database\Migration\Runner\Generators\Entity\EntityQueryBuilderGenerator;
use Avax\Database\Migration\Runner\Generators\Migration\MigrationGenerator;
use Avax\Database\Migration\Runner\Generators\Service\ServiceGenerator;
use Avax\Database\Migration\Runner\Manifest\ManifestStoreInterface;
use Avax\Database\Migration\Runner\Repository\MigrationRepositoryInterface;
use Avax\Database\Migration\Runner\SchemaBuilder;
use Avax\Database\Migration\Runner\Service\MigrationExecution;
use Avax\Database\Migration\Runner\Service\MigrationStateManager;
use Avax\Database\QueryBuilder\QueryBuilder;
use Psr\Log\LoggerInterface;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * Registers all services and commands into the container.
     */
    public function register() : void
    {
        $this->dependencyInjector->singleton(
            abstract: Column::class,
            concrete: static fn() => new Column()
        );

        $this->dependencyInjector->singleton(
            abstract: FieldToDslMapperInterface::class,
            concrete: static fn() => new FluentFieldToDslMapper()
        );

        $this->dependencyInjector->singleton(
            abstract: MigrationGenerator::class,
            concrete: fn() => new MigrationGenerator(
                mapper       : $this->dependencyInjector->get(FieldToDslMapperInterface::class),
                manifestStore: $this->dependencyInjector->get(ManifestStoreInterface::class),
            )
        );

        $this->dependencyInjector->singleton(
            abstract: EntityGenerator::class,
            concrete: static fn() => new EntityGenerator()
        );

        $this->dependencyInjector->singleton(
            abstract: EntityQueryBuilderGenerator::class,
            concrete: static fn() => new EntityQueryBuilderGenerator()
        );

        $this->dependencyInjector->singleton(
            abstract: DtoGenerator::class,
            concrete: static fn() => new DtoGenerator()
        );

        $this->dependencyInjector->singleton(
            abstract: RepositoryGenerator::class,
            concrete: static fn() => new RepositoryGenerator()
        );

        $this->dependencyInjector->singleton(
            abstract: ServiceGenerator::class,
            concrete: static fn() => new ServiceGenerator()
        );

        $this->dependencyInjector->singleton(
            abstract: ControllerGenerator::class,
            concrete: static fn() => new ControllerGenerator()
        );

        // Register the MigrationExecution
        $this->dependencyInjector->singleton(
            abstract: MigrationRepositoryInterface::class,
            concrete: fn() => new MigrationExecution(
                queryBuilder: $this->dependencyInjector->get(QueryBuilder::class),
                logger      : $this->dependencyInjector->get(LoggerInterface::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: MigrationExecution::class,
            concrete: MigrationExecution::class
        );

        // Bind migration state manager
        $this->dependencyInjector->singleton(
            abstract: MigrationStateManager::class,
            concrete: fn() => new MigrationStateManager(
                migrationRepository: $this->dependencyInjector->get(MigrationExecution::class),
                logger             : $this->dependencyInjector->get(LoggerInterface::class)
            )
        );

        // Schema builder and transaction manager
        $this->dependencyInjector->singleton(
            abstract: SchemaBuilder::class,
            concrete: fn() => new SchemaBuilder(
                queryBuilder: $this->dependencyInjector->get(QueryBuilder::class),
                logger      : $this->dependencyInjector->get(LoggerInterface::class)
            )
        );

        // Bind each command to the container
        $this->dependencyInjector->singleton(
            abstract: MakeMigrationCommand::class,
            concrete: fn() => new MakeMigrationCommand(
                migrationGenerator         : $this->dependencyInjector->get(MigrationGenerator::class),
                entityGenerator            : $this->dependencyInjector->get(EntityGenerator::class),
                entityQueryBuilderGenerator: $this->dependencyInjector->get(EntityQueryBuilderGenerator::class),
                dtoGenerator               : $this->dependencyInjector->get(DtoGenerator::class),
                repositoryGenerator        : $this->dependencyInjector->get(RepositoryGenerator::class),
                serviceGenerator           : $this->dependencyInjector->get(ServiceGenerator::class),
                migrationStateManager      : $this->dependencyInjector->get(MigrationStateManager::class),
                logger                     : $this->dependencyInjector->get(LoggerInterface::class)
            )
        );


        $this->dependencyInjector->singleton(
            abstract: MakeControllerCommand::class,
            concrete: fn() => new MakeControllerCommand(
                controllerGenerator: $this->dependencyInjector->get(ControllerGenerator::class),
                logger             : $this->dependencyInjector->get(LoggerInterface::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: MakeEntityCommand::class,
            concrete: fn() => new MakeEntityCommand(
                entityGenerator: $this->dependencyInjector->get(EntityGenerator::class),
                logger         : $this->dependencyInjector->get(LoggerInterface::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: MakeRepositoryCommand::class,
            concrete: fn() => new MakeRepositoryCommand(
                repositoryGenerator: $this->dependencyInjector->get(RepositoryGenerator::class),
                logger             : $this->dependencyInjector->get(LoggerInterface::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: MakeServiceCommand::class,
            concrete: fn() => new MakeServiceCommand(
                serviceGenerator: $this->dependencyInjector->get(ServiceGenerator::class),
                logger          : $this->dependencyInjector->get(LoggerInterface::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: ValidateStubsCommand::class,
            concrete: fn() => new ValidateStubsCommand(
                stubResolver: $this->dependencyInjector->get('stubResolver'),
                logger      : $this->dependencyInjector->get(LoggerInterface::class)
            )
        );
    }

    /**
     * Boots the services (if needed).
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function boot() : void {}
}
