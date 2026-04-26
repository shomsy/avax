<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\NullAuditLog;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\DpopProofReplayStoreInterface;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\TokenCodecInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Verifies a DPoP-like proof and converts it into a sender constraint binding.
 */
final readonly class VerifyDpopProof
{
    private AuditLogInterface $auditLog;

    public function __construct(
        private TokenCodecInterface           $codec,
        private DpopProofReplayStoreInterface $replayStore,
        AuditLogInterface|null                $auditLog = null,
        private int                           $maxAgeSeconds = 300
    )
    {
        $auditLog       ??= new NullAuditLog();
        $this->auditLog = $auditLog;
    }

    /**
     * @throws DpopProofFailed
     * @throws DateMalformedStringException
     */
    public function execute(HttpOAuthProofInput $input) : OAuthSenderConstraint
    {
        $proof = $this->readHeader(headers: $input->headers)
            ?? $this->readServerValue(server: $input->server);

        if ($proof === null || $proof === '') {
            $this->recordFailure(input: $input, reason: 'missing_proof');
            throw DpopProofFailed::missing();
        }

        $claims = $this->codec->decode(token: $proof);

        if (! is_array(value: $claims)) {
            $this->recordFailure(input: $input, reason: 'decode_failed');
            throw DpopProofFailed::invalid(reason: 'decode_failed');
        }

        $jti        = $claims['jti'] ?? null;
        $iat        = $claims['iat'] ?? null;
        $htu        = $claims['htu'] ?? null;
        $htm        = $claims['htm'] ?? null;
        $thumbprint = $claims['jkt'] ?? null;
        $ath        = $claims['ath'] ?? null;

        if (
            ! is_string(value: $jti)
            || ! is_int(value: $iat)
            || ! is_string(value: $htu)
            || ! is_string(value: $htm)
            || ! is_string(value: $thumbprint)
        ) {
            $this->recordFailure(input: $input, reason: 'missing_required_claims');
            throw DpopProofFailed::invalid(reason: 'missing_required_claims');
        }

        $now      = new DateTimeImmutable();
        $issuedAt = new DateTimeImmutable(datetime: "@{$iat}");
        $age      = abs(num: $now->getTimestamp() - $issuedAt->getTimestamp());

        if ($age > $this->maxAgeSeconds) {
            $this->recordFailure(input: $input, reason: 'proof_expired');
            throw DpopProofFailed::invalid(reason: 'proof_expired');
        }

        if (strtoupper(string: $htm) !== strtoupper(string: $input->method)) {
            $this->recordFailure(input: $input, reason: 'method_mismatch');
            throw DpopProofFailed::invalid(reason: 'method_mismatch');
        }

        if (! $this->sameUri(expected: $htu, actual: $input->uri)) {
            $this->recordFailure(input: $input, reason: 'uri_mismatch');
            throw DpopProofFailed::invalid(reason: 'uri_mismatch');
        }

        if ($input->accessToken !== null) {
            $expectedAth = rtrim(
                string    : strtr(base64_encode(string: hash(algo: 'sha256', data: $input->accessToken, binary: true)), '+/', '-_'),
                characters: '='
            );

            if (! is_string(value: $ath) || ! hash_equals(known_string: $expectedAth, user_string: $ath)) {
                $this->recordFailure(input: $input, reason: 'access_token_mismatch');
                throw DpopProofFailed::invalid(reason: 'access_token_mismatch');
            }
        }

        $proofId = hash(algo: 'sha256', data: "{$thumbprint}:{$jti}");

        if (! $this->replayStore->remember(proofId: $proofId, expiresAt: $issuedAt->modify(modifier: "+{$this->maxAgeSeconds} seconds"))) {
            $this->recordFailure(input: $input, reason: 'replay_detected');
            throw DpopProofFailed::invalid(reason: 'replay_detected');
        }

        return new OAuthSenderConstraint(
            type      : OAuthSenderConstraintType::DPOP,
            thumbprint: $thumbprint
        );
    }

    /**
     * @param array<string, mixed> $headers
     */
    private function readHeader(#[SensitiveParameter] array $headers) : string|null
    {
        foreach ($headers as $candidateKey => $value) {
            if (strcasecmp(string1: $candidateKey, string2: 'DPoP') !== 0) {
                continue;
            }

            if (is_array(value: $value)) {
                $value = reset(array: $value);
            }

            return is_scalar(value: $value) ? (string) $value : null;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $server
     */
    private function readServerValue(array $server) : string|null
    {
        $value = $server['HTTP_DPOP'] ?? null;

        return is_scalar(value: $value) ? (string) $value : null;
    }

    private function recordFailure(HttpOAuthProofInput $input, string $reason) : void
    {
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.sender_constraint.dpop.failed',
                                           occurredAt: new DateTimeImmutable(),
                                           context   : [
                                                           'reason' => $reason,
                                                           'method' => strtoupper(string: $input->method),
                                                           'uri'    => $input->uri,
                                                       ]
                                       ));
    }

    private function sameUri(string $expected, string $actual) : bool
    {
        return rtrim(string: $expected, characters: '/') === rtrim(string: $actual, characters: '/');
    }
}
