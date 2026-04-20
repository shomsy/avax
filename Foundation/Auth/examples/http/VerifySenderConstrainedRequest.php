<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Http;

use Avax\Auth\Integrations\Http\HttpOAuthProofInput;
use Avax\Auth\Integrations\Http\VerifyDpopProof;
use Avax\Auth\Integrations\Http\VerifyMtlsSenderConstraint;
use Avax\Auth\Integrations\Http\VerifyOAuthSenderConstraint;
use Avax\Auth\System\Capabilities\OAuth\SenderConstraint\InMemoryDpopProofReplayStore;
use Avax\Auth\System\Capabilities\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capabilities\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Flows\Diagnostics\NullAuditLog;
use Avax\Auth\System\Flows\Token\HmacTokenCodec;
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
        string                            $method,
        string                            $uri,
        #[SensitiveParameter] array       $headers,
        array                             $server,
        #[SensitiveParameter] string|null $accessToken,
        OAuthSenderConstraint|null        $expectedSenderConstraint = null,
        OAuthSenderConstraintType|null    $requiredSenderConstraint = null
    ) : OAuthSenderConstraint|null
    {
        $auditLog = new NullAuditLog();

        return (new VerifyOAuthSenderConstraint(
            verifyDpopProof           : new VerifyDpopProof(
                                            codec      : new HmacTokenCodec(secret: 'replace-me'),
                                            replayStore: new InMemoryDpopProofReplayStore(),
                                            auditLog   : $auditLog
                                        ),
            verifyMtlsSenderConstraint: new VerifyMtlsSenderConstraint(auditLog: $auditLog),
            auditLog                  : $auditLog
        ))->execute(
            input                   : new HttpOAuthProofInput(
                                          method     : $method,
                                          uri        : $uri,
                                          headers    : $headers,
                                          server     : $server,
                                          accessToken: $accessToken
                                      ),
            expectedSenderConstraint: $expectedSenderConstraint,
            requiredSenderConstraint: $requiredSenderConstraint
        );
    }
}
