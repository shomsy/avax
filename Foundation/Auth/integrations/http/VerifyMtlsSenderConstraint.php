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
 * Resolves and verifies mTLS-bound token posture from generic server input.
 */
final readonly class VerifyMtlsSenderConstraint
{
    public function __construct(
        private AuditLogInterface $auditLog = new NullAuditLog()
    ) {}

    /**
     * @throws MtlsBindingFailed
     */
    public function execute(HttpOAuthProofInput $input) : OAuthSenderConstraint
    {
        $thumbprint = $this->readServerValue($input->server, 'TLS_CLIENT_CERT_SHA256')
            ?? $this->readHeader($input->headers, 'x-tls-client-cert-sha256');

        if ($thumbprint === null || $thumbprint === '') {
            $this->recordFailure('missing_certificate', $input);
            throw MtlsBindingFailed::missingCertificate();
        }

        if (
            $input->expectedTokenThumbprint !== null
            && ! hash_equals($input->expectedTokenThumbprint, $thumbprint)
        ) {
            $this->recordFailure('binding_mismatch', $input);
            throw MtlsBindingFailed::mismatch();
        }

        return new OAuthSenderConstraint(
            type      : OAuthSenderConstraintType::MTLS,
            thumbprint: $thumbprint
        );
    }

    /**
     * @param array<string, mixed> $headers
     */
    private function readHeader(array $headers, string $name) : string|null
    {
        foreach ($headers as $candidateKey => $value) {
            if (strcasecmp($candidateKey, $name) !== 0) {
                continue;
            }

            if (is_array($value)) {
                $value = reset($value);
            }

            return is_scalar($value) ? (string) $value : null;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $server
     */
    private function readServerValue(array $server, string $name) : string|null
    {
        $value = $server[$name] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }

    private function recordFailure(string $reason, HttpOAuthProofInput $input) : void
    {
        $this->auditLog->record(new AuditEvent(
            name      : 'auth.oauth.sender_constraint.mtls.failed',
            occurredAt: new DateTimeImmutable(),
            context   : [
                'reason' => $reason,
                'method' => strtoupper($input->method),
                'uri' => $input->uri,
            ]
        ));
    }
}
