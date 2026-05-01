<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Commands\System\PublicSurface;

use Avax\Components\CLI\Commands\System\Capabilities\Generators\CapabilityGeneratorInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MakeActionCommand
{
    public function __construct(
        private CapabilityGeneratorInterface $capabilityGenerator,
        private LoggerInterface $logger,
    ) {}

    public function execute(array $arguments): void
    {
        $name = $arguments['name'] ?? null;
        if (empty($name)) {
            $this->logger->error('Action name is required.');

            return;
        }

        try {
            $this->capabilityGenerator->create($name);
            $this->logger->info(sprintf("Action '%s' created successfully.", $name));
        } catch (Throwable $throwable) {
            $this->logger->error('Error creating action: ' . $throwable->getMessage());
        }
    }
}
