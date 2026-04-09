<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

/**
 * Safe HTTP-facing auth failure payload.
 */
final readonly class HttpAuthFailure
{
    public function __construct(
        public int      $statusCode,
        public string   $errorCode,
        public string   $message,
        public int|null $retryAfterSeconds = null
    ) {}
}
