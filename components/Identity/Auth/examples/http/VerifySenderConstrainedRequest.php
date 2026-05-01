<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Examples\Http;

use Avax\Components\Identity\Auth\Integrations\Http\HttpOAuthProofInput;
use Avax\Components\Identity\Auth\Integrations\Http\VerifyDpopProof;
use Avax\Components\Identity\Auth\Integrations\Http\VerifyMtlsSenderConstraint;
use Avax\Components\Identity\Auth\Integrations\Http\VerifyOAuthSenderConstraint;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\NullAuditLog;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\SenderConstraint\InMemoryDpopProofReplayStore;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\SenderConstraint\OAuthSenderConstraintType;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\HmacTokenCodec;
use SensitiveParameter;

/**
 * Reference adapter for enforcing bearer, DPoP, or mTLS request posture.
 */
final readonly class VerifySenderConstrainedRequest
{
    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     */
    public function execute(
        string                    $method,
        string                    $uri,
        #[SensitiveParameter]
        array                     $headers,
        array                     $server,
        #[SensitiveParameter]
        string|null               $accessToken,
        OAuthSenderConstraint     $expectedSenderConstraint = null,
        OAuthSenderConstraintType|null $requiredSenderConstraint = null,
    ) : OAuthSenderConstraint|null
    {
        $auditLog = new NullAuditLog();

        return new VerifyOAuthSenderConstraint(
            verifyDpopProof           : new VerifyDpopProof(
                                            codec      : new HmacTokenCodec(secret: 'replace-me'),
                                            replayStore: new InMemoryDpopProofReplayStore(),
                                            auditLog   : $auditLog,
                                        ),
            verifyMtlsSenderConstraint: new VerifyMtlsSenderConstraint(auditLog: $auditLog),
            auditLog                  : $auditLog,
        )->execute(
            input                   : new HttpOAuthProofInput(
                                          method     : $method,
                                          uri        : $uri,
                                          headers    : $headers,
                                          server     : $server,
                                          accessToken: $accessToken,
                                      ),
            expectedSenderConstraint: $expectedSenderConstraint,
            requiredSenderConstraint: $requiredSenderConstraint,
        );
    }
}
