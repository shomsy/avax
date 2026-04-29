<?php
declare(strict_types=1);

namespace Avax\Components\CLI\Commands\System\PublicSurface;

use Avax\Components\CLI\Commands\System\Capabilities\Generators\ControllerGeneratorInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MakeControllerCommand
{
    public function __construct(
        private ControllerGeneratorInterface $generator,
        private LoggerInterface $logger
    ) {}

    public function execute(array $arguments): void
    {
        $name = $arguments['name'] ?? null;
        if (empty($name)) {
            $this->logger->error('Controller name is required.');
            return;
        }

        try {
            $this->generator->create($name);
            $this->logger->info("Controller '$name' created successfully.");
        } catch (Throwable $e) {
            $this->logger->error('Error creating controller: ' . $e->getMessage());
        }
    }
}
