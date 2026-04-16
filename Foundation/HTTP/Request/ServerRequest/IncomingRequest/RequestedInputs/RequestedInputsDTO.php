<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\Inputs\DTO\InputsDTO;
use ReflectionException;

/**
 * State Owner: Data Transfer Object encapsulating raw HTTP payloads.
 *
 * This class is now a thin wrapper around InputsDTO for backwards compatibility.
 *
 * @deprecated Use InputsDTO directly for new code.
 */
final class RequestedInputsDTO extends InputsDTO
{
    /**
     * Create a DTO instance from request input slices.
     *
     * @param array<string, mixed> $queryParams
     * @param array|object|null    $parsedBody
     *
     * @return RequestedInputsDTO
     * @throws ReflectionException
     */
    public static function fromSlices(array $queryParams, array|object|null $parsedBody): self
    {
        return new self(data: [
            'query' => $queryParams,
            'body' => self::normalizeBody($parsedBody),
        ]);
    }

    private static function normalizeBody(array|object|null $parsedBody): array
    {
        if ($parsedBody === null) {
            return [];
        }

        if (is_object($parsedBody) && !$parsedBody instanceof self) {
            return (array) $parsedBody;
        }

        return is_array($parsedBody) ? $parsedBody : [];
    }

    /**
     * @deprecated Use query() instead
     * @return array<string, mixed>
     */
    public function getQueryParams(): array
    {
        return $this->query;
    }

    /**
     * @deprecated Use body() instead
     * @return array<string, mixed>
     */
    public function getParsedBody(): array
    {
        return $this->body;
    }
}
