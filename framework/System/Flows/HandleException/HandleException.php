<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleException;

use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use BadMethodCallException;
use Throwable;

final class HandleException
{
    public function handle(Throwable $throwable): RuntimeResponse
    {
        $classification = $this->classifyFrameworkFailure($throwable);

        return match ($classification) {
            'http' => $this->renderHttpFailure($throwable),
            'console' => $this->renderConsoleFailure($throwable),
            default => $this->reportFrameworkFailure($throwable),
        };
    }

    private function classifyFrameworkFailure(Throwable $throwable): string
    {
        if ($throwable instanceof BadMethodCallException) {
            return 'http';
        }

        return 'console';
    }

    private function renderHttpFailure(Throwable $throwable): RuntimeResponse
    {
        return new RuntimeResponse(
            statusCode: 500,
            headers: ['Content-Type' => ['text/plain']],
            body      : $throwable->getMessage(),
        );
    }

    private function renderConsoleFailure(Throwable $throwable): RuntimeResponse
    {
        return new RuntimeResponse(
            statusCode: 0,
            headers: [],
            body      : $throwable->getMessage(),
        );
    }

    private function reportFrameworkFailure(Throwable $throwable): RuntimeResponse
    {
        error_log($throwable->getMessage());

        return new RuntimeResponse(
            statusCode: 1,
            headers: [],
            body      : $throwable->getMessage(),
        );
    }
}
