<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Token;

use Avax\Auth\System\Capabilities\OAuth\SenderConstraint\OAuthSenderConstraint;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Successfully verified workload access token state.
 */
final readonly class ResolvedWorkloadToken
{
    public OAuthSenderConstraint|null $senderConstraint;
    public string|null                $issuer;
    public string|null                $audience;
    /** @var list<string> */
    public array                      $scopes;
    public DateTimeImmutable          $expiresAt;
    public string                     $tokenId;
    public string                     $clientId;
    public string                     $subject;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        string                       $subject,
        string                       $clientId,
        #[SensitiveParameter] string $tokenId,
        DateTimeImmutable            $expiresAt,
        array|null                   $scopes = null,
        string|null                  $audience = null,
        string|null                  $issuer = null,
        OAuthSenderConstraint|null   $senderConstraint = null
    )
    {
        $scopes                 ??= [];
        $this->subject          = $subject;
        $this->clientId         = $clientId;
        $this->tokenId          = $tokenId;
        $this->expiresAt        = $expiresAt;
        $this->scopes           = $scopes;
        $this->audience         = $audience;
        $this->issuer           = $issuer;
        $this->senderConstraint = $senderConstraint;
    }
}
