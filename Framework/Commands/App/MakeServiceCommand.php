<?php

declare(strict_types=1);

namespace Gemini\Commands\App;

use Gemini\Database\Migration\Runner\Generators\Service\ServiceGenerator;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MakeServiceCommand
{
    public function __construct(
        private ServiceGenerator $serviceGenerator,
        private LoggerInterface  $logger
    ) {}

    public function execute(array $arguments) : void
    {
        $name = $arguments['name'] ?? null;

        if (empty($name)) {
            $this->logger->error(message: "Action name is required.");
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
