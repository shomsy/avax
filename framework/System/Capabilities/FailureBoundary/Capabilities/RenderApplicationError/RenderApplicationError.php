<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RenderApplicationError;

use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
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
        private CreateHttpResponse         $createHttpResponse,
        private ClassifyApplicationException $classifier,
    ) {
    }

    public function render(Throwable $e, bool $isProduction = true): ResponseInterface
    {
        $classification = $this->classifier->classify($e);

        if ($isProduction) {
            return $this->createHttpResponse->error(
                message: $classification['safeMessage'],
                status: $classification['statusCode'],
            );
        }

        return $this->createHttpResponse->error(
            message: $e->getMessage(),
            status: $classification['statusCode'],
        );
    }
}
