<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use SensitiveParameter;

/**
 * JSON-ready transport response for optional HTTP adapters.
 */
final readonly class JsonHttpResponse
{
    /** @var array<string, string> */
    public array $headers;
    /** @var array<string, mixed>|list<mixed> */
    public array $body;
    public int   $statusCode;

    /**
     * @param array<string, mixed>|list<mixed> $body
     * @param array<string, string>            $headers
     */
    public function __construct(
        int                         $statusCode,
        array|null                  $body = null,
        #[SensitiveParameter] array $headers = ['Content-Type' => 'application/json']
    )
    {
        $body             ??= [];
        $this->statusCode = $statusCode;
        $this->body       = $body;
        $this->headers    = $headers;
    }
}
