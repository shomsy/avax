<?php

declare(strict_types=1);

namespace Avax\Examples\Auth\Http;

use Avax\Components\Identity\Auth\System\Capabilities\Integrations\Http\HttpOAuthProofInput;
use Avax\Components\Identity\Auth\System\Capabilities\Integrations\Http\VerifyDpopProof;
use Avax\Components\Identity\Auth\System\Capabilities\Integrations\Http\VerifyMtlsSenderConstraint;
use Avax\Components\Identity\Auth\System\Capabilities\Integrations\Http\VerifyOAuthSenderConstraint;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\NullAuditLog;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\InMemoryDpopProofReplayStore;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraintType;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\HmacTokenCodec;
use SensitiveParameter;

final readonly class VerifySenderConstrainedRequest
{
    /**
     * @param  array<string, mixed>  $headers
     * @param  array<string, mixed>  $server
     */
    public function execute(
        string $method,
        string $uri,
        #[SensitiveParameter]
        array $headers,
        array $server,
        #[SensitiveParameter]
        ?string $accessToken, OAuthSenderConstraint|null $oAuthSenderConstraint = null, OAuthSenderConstraintType|null $oAuthSenderConstraintType = null,
    ) : OAuthSenderConstraint|null
    {
        $nullAuditLog = new NullAuditLog();

        return new VerifyOAuthSenderConstraint(
            verifyDpopProof           : new VerifyDpopProof(
                codec      : new HmacTokenCodec(secret: 'replace-me'),
                replayStore: new InMemoryDpopProofReplayStore(),
                auditLog   : $nullAuditLog,
            ),
            verifyMtlsSenderConstraint: new VerifyMtlsSenderConstraint(auditLog: $nullAuditLog),
            auditLog                  : $nullAuditLog,
        )->execute(
            input                   : new HttpOAuthProofInput(
                method     : $method,
                uri        : $uri,
                headers    : $headers,
                server     : $server,
                accessToken: $accessToken,
            ),
            expectedSenderConstraint: $oAuthSenderConstraint,
            requiredSenderConstraint: $oAuthSenderConstraintType,
        );
    }
}
