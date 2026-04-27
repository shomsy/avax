<?php

declare(strict_types=1);

namespace Avax\Components\Framework\System\Flows\HandleException;

use Avax\Components\Framework\System\Capabilities\Runtime\RuntimeResponse;

final class HandleException
{
    public function handle(\Throwable $e): RuntimeResponse
    {
        $classification = $this->classifyFrameworkFailure($e);

        return match ($classification) {
            'http' => $this->renderHttpFailure($e),
            'console' => $this->renderConsoleFailure($e),
            default => $this->reportFrameworkFailure($e),
        };
    }

    private function classifyFrameworkFailure(\Throwable $e): string
    {
        if ($e instanceof \BadMethodCallException) {
            return 'http';
        }

        return 'console';
    }

    private function renderHttpFailure(\Throwable $e): RuntimeResponse
    {
        return new RuntimeResponse(
            statusCode: 500,
            headers: ['Content-Type' => 'text/plain'],
            body: $e->getMessage(),
        );
    }

    private function renderConsoleFailure(\Throwable $e): RuntimeResponse
    {
        return new RuntimeResponse(
            statusCode: 0,
            headers: [],
            body: $e->getMessage(),
        );
    }

    private function reportFrameworkFailure(\Throwable $e): RuntimeResponse
    {
        error_log($e->getMessage());

        return new RuntimeResponse(
            statusCode: 1,
            headers: [],
            body: $e->getMessage(),
        );
    }
}