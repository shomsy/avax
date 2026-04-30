<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Http;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\NullAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraintType;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Resolves and verifies mTLS-bound token posture from generic server input.
 */
final readonly class VerifyMtlsSenderConstraint
{
    public function __construct(private AuditLogInterface $auditLog = new NullAuditLog()) {}

    /**
     * @throws MtlsBindingFailed
     */
    public function execute(HttpOAuthProofInput $input) : OAuthSenderConstraint
    {
        $thumbprint = $this->readServerValue(server: $input->server)
            ?? $this->readHeader(headers: $input->headers);

        if ($thumbprint === null || $thumbprint === '') {
            $this->recordFailure(reason: 'missing_certificate', input: $input);

            throw MtlsBindingFailed::missingCertificate();
        }

        if (
            $input->expectedTokenThumbprint !== null
            && ! hash_equals(known_string: $input->expectedTokenThumbprint, user_string: $thumbprint)
        ) {
            $this->recordFailure(reason: 'binding_mismatch', input: $input);

            throw MtlsBindingFailed::mismatch();
        }

        return new OAuthSenderConstraint(
            type      : OAuthSenderConstraintType::MTLS,
            thumbprint: $thumbprint,
        );
    }

    /**
     * @param array<string, mixed> $server
     */
    private function readServerValue(array $server) : string|null
    {
        $value = $server['TLS_CLIENT_CERT_SHA256'] ?? null;

        return is_scalar(value: $value) ? (string) $value : null;
    }

    /**
     * @param array<string, mixed> $headers
     */
    private function readHeader(#[SensitiveParameter] array $headers) : string|null
    {
        foreach ($headers as $candidateKey => $value) {
            if (strcasecmp(string1: $candidateKey, string2: 'x-tls-client-cert-sha256') !== 0) {
                continue;
            }

            if (is_array(value: $value)) {
                $value = reset(array: $value);
            }

            return is_scalar(value: $value) ? (string) $value : null;
        }

        return null;
    }

    private function recordFailure(string $reason, HttpOAuthProofInput $input) : void
    {
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.sender_constraint.mtls.failed',
                                           occurredAt: new DateTimeImmutable(),
                                           context   : [
                                                           'reason' => $reason,
                                                           'method' => strtoupper(string: $input->method),
                                                           'uri'    => $input->uri,
                                                       ],
                                       ));
    }
}
