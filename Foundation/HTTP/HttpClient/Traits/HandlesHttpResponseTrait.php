<?php

declare(strict_types=1);

namespace Avax\HTTP\HttpClient\Traits;

use JsonException;
use Psr\Http\Message\ResponseInterface;

trait HandlesHttpResponseTrait
{
    /**
     * Decodes and formats the HTTP response.
     *
     * @param  ResponseInterface  $response  The HTTP response.
     * @param  string  $endpoint  The endpoint URL.
     * @return ResponseInterface A formatted response object.
     *
     * @throws JsonException
     */
    public function getResponse(ResponseInterface $response, string $endpoint): ResponseInterface
    {
        $body = $response->getBody()->getContents();
        $decoded = json_decode(json: $body, associative: true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->dataLogger->warning(
                message: 'HTTPClient Error! JSON decoding failed: '.json_last_error_msg(),
                context: ['body' => $body],
            );
            $decoded = $body;
        }

        $formattedResult = [
            'endpoint' => $endpoint,
            'result' => $decoded ?? $body,
        ];

        return $this->responseFactory->json(data: $formattedResult);
    }
}
