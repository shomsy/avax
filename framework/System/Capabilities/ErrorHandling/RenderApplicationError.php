<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ErrorHandling;

use Avax\Components\HTTP\Response\ResponseFactory;
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
        private ResponseFactory $responseFactory = new ResponseFactory(),
        private ClassifyApplicationException $classifier = new ClassifyApplicationException(),
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
