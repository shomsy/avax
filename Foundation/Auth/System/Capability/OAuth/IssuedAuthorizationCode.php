<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * One-time authorization code returned to the client redirect layer.
 */
final readonly class IssuedAuthorizationCode
{
    public string|null       $state;
    public DateTimeImmutable $expiresAt;
    public string            $codeId;
    public string            $code;

    public function __construct(
        #[SensitiveParameter] string $code,
        #[SensitiveParameter] string $codeId,
        DateTimeImmutable            $expiresAt,
        string|null                  $state = null
    )
    {
        $this->code      = $code;
        $this->codeId    = $codeId;
        $this->expiresAt = $expiresAt;
        $this->state     = $state;
    }
}
