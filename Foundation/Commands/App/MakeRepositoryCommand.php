<?php

declare(strict_types=1);

namespace Avax\Commands\App;

use Avax\Commands\App\Contracts\RepositoryGeneratorInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MakeRepositoryCommand
{
    private LoggerInterface     $logger;
    private RepositoryGeneratorInterface $repositoryGenerator;

    public function __construct(
        RepositoryGeneratorInterface $repositoryGenerator,
        LoggerInterface              $logger
    )
    {
        $this->repositoryGenerator = $repositoryGenerator;
        $this->logger              = $logger;
    }

    public function execute(array $arguments) : void
    {
        $name   = $arguments['name'] ?? null;
        $entity = $arguments['entity'] ?? null;

        if (empty($name) || empty($entity)) {
            $this->logger->error(message: 'Repository name and entity are required.');
            echo "Error: Repository name and entity are required.\n";

            return;
        }

        try {
            $this->repositoryGenerator->create(tableName: $name, entity: $entity);
            $this->logger->info(
                message: sprintf(
                             "Repository '%s' for entity '%s' created successfully.",
                             $name,
                             $entity
                         )
            );
        } catch (Throwable $throwable) {
            $this->logger->error(message: 'Error creating repository: ' . $throwable->getMessage());
        }
    }
}
