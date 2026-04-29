<?php
declare(strict_types=1);

namespace Avax\Components\CLI\Commands\System\PublicSurface;

use Avax\Components\CLI\Commands\System\Capabilities\Generators\ServiceGeneratorInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MakeServiceCommand
{
    public function __construct(
        private ServiceGeneratorInterface $generator,
        private LoggerInterface $logger
    ) {}

    public function execute(array $arguments): void
    {
        $name = $arguments['name'] ?? null;
        if (empty($name)) {
            $this->logger->error('Service name is required.');
            return;
        }

        try {
            $this->generator->create($name);
            $this->logger->info("Service '$name' created successfully.");
        } catch (Throwable $e) {
            $this->logger->error('Error creating service: ' . $e->getMessage());
        }
    }
}
