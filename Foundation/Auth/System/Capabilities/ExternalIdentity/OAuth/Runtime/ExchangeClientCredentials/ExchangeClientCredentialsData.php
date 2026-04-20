<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\OAuth\ExchangeClientCredentials;

use Avax\Auth\System\Capabilities\OAuth\SenderConstraint\OAuthSenderConstraint;
use SensitiveParameter;

final readonly class ExchangeClientCredentialsData
{
    public OAuthSenderConstraint|null $senderConstraint;
    public string|null                $userAgent;
    public string|null                $ipAddress;
    public string|null                $audience;
    /** @var list<string> */
    public array                      $scopes;
    public string|null                $clientSecret;
    public string                     $clientId;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        string                             $clientId,
        #[SensitiveParameter] string|null  $clientSecret = null,
        array|null                         $scopes = null,
        string|null                        $audience = null,
        #[\SensitiveParameter] string|null $ipAddress = null,
        string|null                        $userAgent = null,
        OAuthSenderConstraint|null         $senderConstraint = null
    )
    {
        $scopes                 ??= [];
        $this->clientId         = $clientId;
        $this->clientSecret     = $clientSecret;
        $this->scopes           = $scopes;
        $this->audience         = $audience;
        $this->ipAddress        = $ipAddress;
        $this->userAgent        = $userAgent;
        $this->senderConstraint = $senderConstraint;
    }
}
