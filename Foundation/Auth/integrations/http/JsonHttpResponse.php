<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use SensitiveParameter;

/**
 * JSON-ready transport response for optional HTTP adapters.
 */
final readonly class JsonHttpResponse
{
    /** @var array<string, mixed>|list<mixed> */
    public array $body;

    /**
     * @param array<string, mixed>|list<mixed> $body
     * @param array<string, string>            $headers
     */
    public function __construct(
        public int                         $statusCode,
        array|null                  $body = null,
        #[SensitiveParameter] public array $headers = ['Content-Type' => 'application/json']
    )
    {
        $body             ??= [];
        $this->body       = $body;
    }
}
