<?php

declare(strict_types=1);

namespace Avax\HTTP\Request;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use ReflectionException;

/**
 * RequestDtoFactory
 *
 * Specialized factory for creating Request-backed DTOs (FormRequests).
 * Ensures the underlying ServerRequest is properly attached.
 */
final readonly class RequestDtoFactory
{
    /**
     * Create a Request DTO instance from ServerRequest.
     *
     * @template T of Request
     * @param class-string<T> $requestClass
     * @return T
     * @throws ReflectionException
     */
    public function create(ServerRequest $serverRequest, string $requestClass): Request
    {
        /** @var T $instance */
        return $requestClass::fromRequest(request: $serverRequest);
    }
}
