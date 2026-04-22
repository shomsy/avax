<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use SensitiveParameter;

/**
 * Safe HTTP-facing auth failure payload.
 */
final readonly class HttpAuthFailure
{
    public function __construct(
        public int                          $statusCode,
        #[SensitiveParameter] public string $errorCode,
        public string                       $message,
        public int|null                     $retryAfterSeconds = null
    )
    {
    }
}
