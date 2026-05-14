<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RenderApplicationError;

use Avax\Components\HTTP\System\Capabilities\ResponseBuilding\ResponseFactory;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ClassifyApplicationException\ClassifyApplicationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * RenderApplicationError — Renders classified exceptions into safe HTTP responses.
 *
 * In production mode: only safe message + status code.
 * In development mode: full error message + status code.
 */
final readonly class RenderApplicationError
{
    public function __construct(
        private ResponseFactory              $responseFactory,
        private ClassifyApplicationException $classifier,
    ) {
    }

    public function render(Throwable $e, bool $isProduction = true): ResponseInterface
    {
        $classification = $this->classifier->classify($e);

        if ($isProduction) {
            return $this->responseFactory->createErrorResponse(
                message: $classification['safeMessage'],
                statusCode: $classification['statusCode'],
            );
        }

        return $this->responseFactory->createErrorResponse(
            message: $e->getMessage(),
            statusCode: $classification['statusCode'],
        );
    }
}
