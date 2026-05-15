<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ResponseNormalization;

use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Stringable;

/**
 * NormalizeControllerResult — Normalizes controller return values into HTTP responses.
 *
 * Rules:
 * - ResponseInterface: returned as-is
 * - string: becomes text response
 * - array: becomes JSON response
 * - DataObject: becomes JSON response via DataTransfer serialization
 * - JsonSerializable: becomes JSON response
 * - null: becomes empty 200 response
 */
final readonly class NormalizeControllerResult
{
    public function __construct(
        private CreateHttpResponse $createHttpResponse,
    ) {
    }

    public function normalize(mixed $result): ResponseInterface
    {
        if ($result === null) {
            return $this->createHttpResponse->create(body: '');
        }

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        if (is_string($result)) {
            return $this->createHttpResponse->create(body: $result);
        }

        if ($result instanceof DataObject) {
            return $this->createHttpResponse->json(data: $result->toArray());
        }

        if (is_array($result) || $result instanceof JsonSerializable) {
            return $this->createHttpResponse->json(data: $result);
        }

        if ($result instanceof Stringable) {
            return $this->createHttpResponse->create(body: (string) $result);
        }

        // Fallback: wrap scalar values and convert to JSON
        return $this->createHttpResponse->json(data: ['value' => $result]);
    }
}
