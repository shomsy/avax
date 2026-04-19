<?php

declare(strict_types=1);

namespace Avax\HTTP\Request;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
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
        $instance = new static(data: $request->inputs()->all());
        $instance->serverRequest = $request;

        return $instance;
    }

    public function getMethod() : string
    {
        return $this->serverRequest->getMethod();
    }

    public function withMethod(string $method) : static
    {
        return clone($this, [
            "serverRequest" => $this->serverRequest->withMethod($method)
        ]);
    }

    public function getUri() : UriInterface
    {
        return $this->serverRequest->getUri();
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false) : static
    {
        return clone($this, [
            "serverRequest" => $this->serverRequest->withUri($uri, $preserveHost)
        ]);
    }

    public function getHeader(string $name) : array
    {
        return $this->serverRequest->getHeader($name);
    }

    public function getHeaderLine(string $name) : string
    {
        return $this->serverRequest->getHeaderLine($name);
    }

    public function withHeader(string $name, $value) : static
    {
        return clone($this, [
            "serverRequest" => $this->serverRequest->withHeader($name, $value)
        ]);
    }

    public function withAddedHeader(string $name, $value) : static
    {
        return clone($this, [
            "serverRequest" => $this->serverRequest->withAddedHeader($name, $value)
        ]);
    }

    public function withoutHeader(string $name) : static
    {
        return clone($this, [
            "serverRequest" => $this->serverRequest->withoutHeader($name)
        ]);
    }

    public function hasHeader(string $name) : bool
    {
        return $this->serverRequest->hasHeader($name);
    }

    public function getRequestTarget() : string
    {
        return $this->serverRequest->getRequestTarget();
    }

    public function withRequestTarget(string $requestTarget) : static
    {
        return clone($this, [
            "serverRequest" => $this->serverRequest->withRequestTarget($requestTarget)
        ]);
    }

    public function getParsedBody() : array|null|object
    {
        return $this->serverRequest->getParsedBody();
    }

    public function withParsedBody($data) : static
    {
        return clone($this, [
            "serverRequest" => $this->serverRequest->withParsedBody($data)
        ]);
    }

    /**
     * Create a Request DTO from raw input array.
     *
     * @throws ReflectionException
     */
    public static function fromInputs(array $inputs) : static
    {
        return new static(data: $inputs);
    }
}
