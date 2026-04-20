<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\CompleteFederatedLogin;

use SensitiveParameter;

final readonly class CompleteFederatedLoginData
{
    public string|null $userAgent;
    public string|null $ipAddress;
    /** @var array<string, mixed> */
    public array       $payload;
    public string      $connectionId;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        string                            $connectionId,
        array                             $payload,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $this->connectionId = $connectionId;
        $this->payload      = $payload;
        $this->ipAddress    = $ipAddress;
        $this->userAgent    = $userAgent;
    }
}
