<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use SensitiveParameter;

/**
 * Safe HTTP-facing auth failure payload.
 */
final readonly class HttpAuthFailure
{
    public int|null $retryAfterSeconds;
    public string   $message;
    public string   $errorCode;
    public int      $statusCode;

    public function __construct(
        int                          $statusCode,
        #[SensitiveParameter] string $errorCode,
        string                       $message,
        int|null                     $retryAfterSeconds = null
    )
    {
        $this->statusCode        = $statusCode;
        $this->errorCode         = $errorCode;
        $this->message           = $message;
        $this->retryAfterSeconds = $retryAfterSeconds;
    }
}
