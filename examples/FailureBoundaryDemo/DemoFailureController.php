<?php

declare(strict_types=1);

namespace Avax\Examples\FailureBoundaryDemo;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\OnFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\ReportFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\Retry;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

/**
 * DemoFailureController — Demonstrates real FailureBoundary attribute adoption.
 *
 * This controller proves that FailureBoundary attributes are not decorative:
 * they affect real runtime behavior when exceptions are thrown.
 */
final readonly class DemoFailureController
{
    /**
     * Throws a validation error that maps to 422.
     *
     * @throws \InvalidArgumentException
     */
    #[OnFailure(\InvalidArgumentException::class, respondWith: 422, messageKey: 'validation_failed')]
    #[ReportFailure(channel: 'http')]
    public function throwsValidation(): ResponseInterface
    {
        throw new \InvalidArgumentException('Email is required');
    }

    /**
     * Throws a runtime error that maps to 503.
     *
     * @throws \RuntimeException
     */
    #[OnFailure(\RuntimeException::class, respondWith: 503, messageKey: 'service_unavailable')]
    #[Retry(maxAttempts: 2, backoff: 'none')]
    public function throwsRuntime(): ResponseInterface
    {
        throw new \RuntimeException('External API is down');
    }

    /**
     * Throws an unreported exception that should propagate.
     *
     * @throws \LogicException
     */
    public function throwsUnhandled(): ResponseInterface
    {
        throw new \LogicException('This should propagate');
    }

    /**
     * Returns a successful response (no exception).
     */
    public function returnsSuccess(): Response
    {
        return Response::json(
            ['status' => 'ok'],
            200,
        );
    }
}
