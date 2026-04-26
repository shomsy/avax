<?php

declare(strict_types=1);

namespace Avax\HTTP\Request;

use Avax\DataFoundation\ObjectHandling\DTO\AbstractDTO;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use LogicException;
use Psr\Http\Message\UriInterface;
use ReflectionException;

/**
 * Request - Base class for HTTP Request-backed DTOs.
 *
 * Enables declarative request handling using PHP 8 attributes.
 * Automatically hydrates from ServerRequest inputs with validation.
 *
 * Usage:
 * ```php
 * class CreateNewProjectRequest extends \Avax\HTTP\Request\Request
 * {
 *     #[Required]
 *     #[Min(3)]
 *     public string $projectName;
 *
 *     #[Required]
 *     #[Integer]
 *     #[Min(1)]
 *     public int $projectNumber;
 * }
 *
 * // Usage in controller:
 * $request = CreateNewProjectRequest::fromRequest($httpRequest);
 * $request->projectName; // already validated!
 * ```
 */
abstract class Request extends AbstractDTO
{
    protected ServerRequest $serverRequest;

    /**
     * Create a Request DTO instance from ServerRequest.
     *
     * @throws ReflectionException
     */
    public static function fromRequest(ServerRequest $request) : static
    {
        $instance                = new static(data: $request->inputs()->all());
        $instance->serverRequest = $request;

        return $instance;
    }

    /**
     * Create a Request DTO from raw input array (detached from ServerRequest).
     *
     * @throws ReflectionException
     */
    public static function fromInputs(array $inputs) : static
    {
        return new static(data: $inputs);
    }

    /**
     * Get the underlying PSR-7 server request.
     */
    public function serverRequest() : ServerRequest
    {
        return $this->requireServerRequest();
    }

    private function requireServerRequest() : ServerRequest
    {
        if (! isset($this->serverRequest)) {
            throw new LogicException(
                message: 'Request is detached from ServerRequest. Use fromRequest() or RequestDtoFactory to create an attached request.'
            );
        }

        return $this->serverRequest;
    }

    /**
     * Get the HTTP method.
     */
    public function method() : string
    {
        return $this->requireServerRequest()->getMethod();
    }

    /**
     * Get the request URI.
     */
    public function uri() : UriInterface
    {
        return $this->requireServerRequest()->getUri();
    }

    /**
     * Get a specific header line.
     */
    public function header(string $name) : string
    {
        return $this->requireServerRequest()->getHeaderLine(name: $name);
    }

    /**
     * Get the client IP address.
     */
    public function clientAddress() : string|null
    {
        $serverRequest = $this->requireServerRequest();

        return $serverRequest->resolveClientAddress(serverParams: $serverRequest->getServerParams());
    }
}
