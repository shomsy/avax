<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\ResponseNormalization;

use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

final readonly class NormalizeControllerResult
{
    public function normalize(mixed $result) : Response
    {
        if ($result instanceof ResponseInterface) {
            /** @var Response $response */
            $response = $result;

            return $response;
        }

        if (is_string($result)) {
            return Response::text($result);
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        return Response::text((string) $result);
    }
}
