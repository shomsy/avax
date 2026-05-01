<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;

final readonly class RuntimeRequest
{
    private string $method;

    private string $uri;

    /**
     * @var array<string, list<string>>
     */
    private array $headers;

    private ?string $body;

    /**
     * @var array<string, mixed>
     */
    private array $attributes;

    /**
     * @param array<string, list<string>> $headers
     * @param array<string, mixed>        $attributes
     */
    public function __construct(
        string $method,
        string $uri,
        array $headers = [],
        ?string $body = null,
        array $attributes = [],
    ) {
        $normalizedMethod = strtoupper(string: trim(string: $method));
        $normalizedUri = trim(string: $uri);

        if ($normalizedMethod === '') {
            throw new FrameworkMisconfigured(message: 'Runtime request method cannot be empty.');
        }

        if ($normalizedUri === '') {
            throw new FrameworkMisconfigured(message: 'Runtime request uri cannot be empty.');
        }

        $this->method  = $normalizedMethod;
        $this->uri     = $normalizedUri;
        $this->headers = $headers;
        $this->body    = $body;
        $this->attributes = $attributes;
    }

    public function method() : string
    {
        return $this->method;
    }

    public function uri() : string
    {
        return $this->uri;
    }

    /**
     * @return array<string, list<string>>
     */
    public function headers() : array
    {
        return $this->headers;
    }

    public function body() : ?string
    {
        return $this->body;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes() : array
    {
        return $this->attributes;
    }
}
