<?php

declare(strict_types=1);

namespace Avax\Commands\App;

use Avax\Commands\App\Contracts\ControllerGeneratorInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MakeControllerCommand
{
    private LoggerInterface     $logger;
    private ControllerGeneratorInterface $controllerGenerator;

    public function __construct(
        ControllerGeneratorInterface $controllerGenerator,
        LoggerInterface              $logger
    )
    {
        $this->controllerGenerator = $controllerGenerator;
        $this->logger              = $logger;
    }

    public function execute(array $arguments) : void
    {
        $name = $arguments['name'] ?? null;

        if (empty($name)) {
            $this->logger->error(message: 'Controller name is required.');
            echo "Error: Controller name is required.\n";

            return;
        }

        try {
            $this->controllerGenerator->create(name: $name);
            $this->logger->info(message: sprintf("Controller '%s' created successfully.", $name));
        } catch (Throwable $throwable) {
            $this->logger->error(message: 'Error creating controller: ' . $throwable->getMessage());
        }
    }
}
