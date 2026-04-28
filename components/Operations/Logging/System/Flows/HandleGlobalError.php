<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Flows;

use Avax\Components\Operations\Logging\System\PublicSurface\Log;
use ErrorException;
use Throwable;

/**
 * Flow to handle global application errors and exceptions.
 * Recovered from legacy ErrorHandler.
 */
final readonly class HandleGlobalError
{
    public function __construct(private Log $logger) {}

    public function execute(Throwable $throwable) : void
    {
        $this->logger->error($throwable->getMessage(), [
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine()
        ]);

        // Basic JSON rendering for now (Screaming architecture: logic stays in flow)
        if (! headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(500);
        }

        echo json_encode([
                             'status'  => 500,
                             'message' => 'Internal Server Error',
                             'error'   => $throwable->getMessage()
                         ]);
    }

    public function convertErrorToException(int $severity, string $message, string $file, int $line) : never
    {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
}
