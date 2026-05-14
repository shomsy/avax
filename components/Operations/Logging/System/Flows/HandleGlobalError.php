<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Flows;

use Avax\Components\Operations\Logging\System\PublicSurface\Logging;
use ErrorException;
use Throwable;

final readonly class HandleGlobalError
{
    public function __construct(private Logging $logging)
    {
    }

    public function execute(Throwable $throwable): void
    {
        $this->logging->error($throwable->getMessage(), [
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTraceAsString(),
        ]);

        if (! headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(500);
        }

        echo json_encode([
            'status' => 500,
            'message' => 'Internal Server Error',
            'error' => $throwable->getMessage(),
        ]);
    }

    /**
 * @throws ErrorException
 */
public function convertErrorToException(int $severity, string $message, string $file, int $line): never
    {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
}
