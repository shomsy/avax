<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\CompleteAuthentication;

use SensitiveParameter;

final readonly class CompletePasskeyAuthenticationData
{
    public string|null $userAgent;
    public string|null $ipAddress;
    public array       $response;
    public string      $challengeId;

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(
        string                            $challengeId,
        array                             $response,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $this->challengeId = $challengeId;
        $this->response    = $response;
        $this->ipAddress   = $ipAddress;
        $this->userAgent   = $userAgent;
    }
}
