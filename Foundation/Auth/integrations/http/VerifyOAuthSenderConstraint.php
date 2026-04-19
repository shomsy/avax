<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Diagnostics\NullAuditLog;
use DateTimeImmutable;

/**
 * Enforces bearer, DPoP, or mTLS policy for a verified OAuth token.
 */
final readonly class VerifyOAuthSenderConstraint
{
    private AuditLogInterface          $auditLog;
    private VerifyMtlsSenderConstraint $verifyMtlsSenderConstraint;
    private VerifyDpopProof            $verifyDpopProof;

    public function __construct(
        VerifyDpopProof            $verifyDpopProof,
        VerifyMtlsSenderConstraint $verifyMtlsSenderConstraint,
        AuditLogInterface          $auditLog = new NullAuditLog()
    )
    {
        $this->verifyDpopProof            = $verifyDpopProof;
        $this->verifyMtlsSenderConstraint = $verifyMtlsSenderConstraint;
        $this->auditLog                   = $auditLog;
    }

    /**
     * @param HttpOAuthProofInput            $input
     * @param OAuthSenderConstraint|null     $expectedSenderConstraint
     * @param OAuthSenderConstraintType|null $requiredSenderConstraint
     *
     * @return OAuthSenderConstraint|null
     * @throws \DateMalformedStringException
     */
    public function execute(
        HttpOAuthProofInput            $input,
        OAuthSenderConstraint|null     $expectedSenderConstraint = null,
        OAuthSenderConstraintType|null $requiredSenderConstraint = null
    ) : OAuthSenderConstraint|null
    {
        $requiredType = $expectedSenderConstraint?->type ?? $requiredSenderConstraint;

        if ($requiredType === null) {
            return null;
        }

        $binding = match ($requiredType) {
            OAuthSenderConstraintType::DPOP => $this->verifyDpopProof->execute(input: $input),
            OAuthSenderConstraintType::MTLS => $this->verifyMtlsSenderConstraint->execute(input: new HttpOAuthProofInput(
                                                                                                     method                 : $input->method,
                                                                                                     uri                    : $input->uri,
                                                                                                     headers                : $input->headers,
                                                                                                     server                 : $input->server,
                                                                                                     accessToken            : $input->accessToken,
                                                                                                     expectedTokenThumbprint: $expectedSenderConstraint?->thumbprint
                                                                                                 )),
        };

        if ($expectedSenderConstraint !== null && ! $expectedSenderConstraint->equals(other: $binding)) {
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.oauth.sender_constraint.mismatch',
                                               occurredAt: new DateTimeImmutable(),
                                               context   : [
                                                               'expected_type'       => $expectedSenderConstraint->type->value,
                                                               'expected_thumbprint' => $expectedSenderConstraint->thumbprint,
                                                               'actual_type'         => $binding->type->value,
                                                               'actual_thumbprint'   => $binding->thumbprint,
                                                               'method'              => strtoupper($input->method),
                                                               'uri'                 => $input->uri,
                                                           ]
                                           ));

            throw SenderConstraintVerificationFailed::mismatch();
        }

        return $binding;
    }
}
