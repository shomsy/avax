<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Commands;

use Avax\Database\Migration\Runner\Generators\CommandInterface;
use Avax\Database\Migration\Runner\Generators\StubResolver;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * ValidateStubsCommand Class
 *
 * Validates the existence and readability of stub files in the specified directory.
 */
readonly class ValidateStubsCommand implements CommandInterface
{
    public function __construct(
        private StubResolver    $stubResolver,
        private LoggerInterface $logger
    ) {}

    /**
     * Executes the stub validation command.
     *
     * @param array $arguments List of stub file names to validate.
     */
    public function execute(array $arguments) : void
    {
        if ($arguments === []) {
            $this->logger->error(message: "No stub files provided for validation.");
            echo "Error: No stub files provided for validation.\n";

            return;
        }

        foreach ($arguments as $argument) {
            try {
                // Attempt to read the stub file
                $this->stubResolver->read(stubName: $argument);

                // Log and output success message
                $this->logger->info(message: sprintf('Stub "%s" is valid.', $argument));
                echo sprintf("Stub \"%s\" is valid.\n", $argument);
            } catch (Throwable $e) {
                // Log and output error message
                $this->logger->error(
                    message: sprintf(
                                 'Stub "%s" validation failed: %s',
                                 $argument,
                                 $e->getMessage()
                             )
                );
                echo sprintf(
                    "Error: Stub \"%s\" validation failed: %s\n",
                    $argument,
                    $e->getMessage()
                );
            }
        }
    }
}
