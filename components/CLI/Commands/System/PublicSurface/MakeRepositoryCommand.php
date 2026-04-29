<?php
declare(strict_types=1);

namespace Avax\Components\CLI\Commands\System\PublicSurface;

use Avax\Components\CLI\Commands\System\Capabilities\Generators\RepositoryGeneratorInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MakeRepositoryCommand
{
    public function __construct(
        private RepositoryGeneratorInterface $generator,
        private LoggerInterface $logger
    ) {}

    public function execute(array $arguments): void
    {
        $name = $arguments['name'] ?? null;
        $entity = $arguments['entity'] ?? null;

        if (empty($name) || empty($entity)) {
            $this->logger->error('Repository name and entity are required.');
            return;
        }

        try {
            $this->generator->create($name, $entity);
            $this->logger->info("Repository '$name' created successfully.");
        } catch (Throwable $e) {
            $this->logger->error('Error creating repository: ' . $e->getMessage());
        }
    }
}
