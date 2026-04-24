<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\BuildResponse;

use Avax\HTTP\Response\Capabilities\Body\Json\EncodeJsonBody;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class BuildJsonResponse
{
    public function __invoke(mixed $data, int|null $status = null, array $headers = []) : ResponseInterface
    {
        $status ??= 200;
        try {
            $payload = new EncodeJsonBody()($data);
        } catch (JsonException $exception) {
            throw new RuntimeException(
                message : 'Unable to encode JSON response body.',
                code    : $exception->getCode(),
                previous: $exception,
            );
        }

        return new BuildResponse()(
            status : $status,
            headers: ['Content-Type' => 'application/json', ...$headers],
            body   : $payload,
        );
    }
}
