<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\ResponseNormalization;

use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

final readonly class NormalizeControllerResult
{
    public function __construct(
        private CreateHttpResponse $createHttpResponse,
    ) {
    }

    public function normalize(mixed $result): Response
    {
        if ($result instanceof ResponseInterface) {
            /** @var Response $response */
            $response = $result;

            return $response;
        }

        if (is_string($result)) {
            return $this->createHttpResponse->text(content: $result);
        }

        if (is_array($result)) {
            return $this->createHttpResponse->json(data: $result);
        }

        return $this->createHttpResponse->text(content: (string) $result);
    }
}
