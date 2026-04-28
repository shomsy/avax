<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\App;

use Avax\Components\CLI\Console\App\Contracts\ServiceGeneratorInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MakeServiceCommand
{
    private LoggerInterface           $logger;
    private ServiceGeneratorInterface $serviceGenerator;

    public function __construct(
        ServiceGeneratorInterface $serviceGenerator,
        LoggerInterface           $logger
    )
    {
        $this->serviceGenerator = $serviceGenerator;
        $this->logger           = $logger;
    }

    public function execute(array $arguments) : void
    {
        $name = $arguments['name'] ?? null;

        if (empty($name)) {
            $this->logger->error(message: 'Action name is required.');
            echo "Error: Action name is required.\n";

            return;
        }

        try {
            $this->serviceGenerator->create(name: $name);
            $this->logger->info(message: sprintf("Action '%s' created successfully.", $name));
        } catch (Throwable $throwable) {
            $this->logger->error(message: 'Error creating service: ' . $throwable->getMessage());
        }
    }
}
