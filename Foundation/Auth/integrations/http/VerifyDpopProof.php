<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\DpopProofReplayStoreInterface;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Diagnostics\NullAuditLog;
use Avax\Auth\System\Flow\Token\TokenCodecInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Verifies a DPoP-like proof and converts it into a sender constraint binding.
 */
final readonly class VerifyDpopProof
{
    private int                           $maxAgeSeconds;
    private AuditLogInterface             $auditLog;
    private DpopProofReplayStoreInterface $replayStore;
    private TokenCodecInterface           $codec;

    public function __construct(
        TokenCodecInterface           $codec,
        DpopProofReplayStoreInterface $replayStore,
        AuditLogInterface|null        $auditLog = null,
        int                           $maxAgeSeconds = 300
    )
    {
        $auditLog            ??= new NullAuditLog();
        $this->codec         = $codec;
        $this->replayStore   = $replayStore;
        $this->auditLog      = $auditLog;
        $this->maxAgeSeconds = $maxAgeSeconds;
    }

    /**
     * @throws DpopProofFailed
     * @throws DateMalformedStringException
     */
    public function execute(HttpOAuthProofInput $input) : OAuthSenderConstraint
    {
        $proof = $this->readHeader(headers: $input->headers, name: 'DPoP')
            ?? $this->readServerValue(server: $input->server, name: 'HTTP_DPOP');

        if ($proof === null || $proof === '') {
            $this->recordFailure(input: $input, reason: 'missing_proof');
            throw DpopProofFailed::missing();
        }

        $claims = $this->codec->decode(token: $proof);

        if (! is_array($claims)) {
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
            ! is_string($jti)
            || ! is_int($iat)
            || ! is_string($htu)
            || ! is_string($htm)
            || ! is_string($thumbprint)
        ) {
            $this->recordFailure(input: $input, reason: 'missing_required_claims');
            throw DpopProofFailed::invalid(reason: 'missing_required_claims');
        }

        $now      = new DateTimeImmutable();
        $issuedAt = new DateTimeImmutable(datetime: "@{$iat}");
        $age      = abs($now->getTimestamp() - $issuedAt->getTimestamp());

        if ($age > $this->maxAgeSeconds) {
            $this->recordFailure(input: $input, reason: 'proof_expired');
            throw DpopProofFailed::invalid(reason: 'proof_expired');
        }

        if (strtoupper($htm) !== strtoupper($input->method)) {
            $this->recordFailure(input: $input, reason: 'method_mismatch');
            throw DpopProofFailed::invalid(reason: 'method_mismatch');
        }

        if (! $this->sameUri(expected: $htu, actual: $input->uri)) {
            $this->recordFailure(input: $input, reason: 'uri_mismatch');
            throw DpopProofFailed::invalid(reason: 'uri_mismatch');
        }

        if ($input->accessToken !== null) {
            $expectedAth = hash('sha256', $input->accessToken, true)
                    |> base64_encode(...)
                    |> (static fn ($x) => strtr($x, '+/', '-_'))
                    |> (static fn ($x) => rtrim($x, '='));

            if (! is_string($ath) || ! hash_equals($expectedAth, $ath)) {
                $this->recordFailure(input: $input, reason: 'access_token_mismatch');
                throw DpopProofFailed::invalid(reason: 'access_token_mismatch');
            }
        }

        $proofId = hash('sha256', "{$thumbprint}:{$jti}");

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
    private function readHeader(#[SensitiveParameter] array $headers, string $name) : string|null
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

    private function recordFailure(HttpOAuthProofInput $input, string $reason) : void
    {
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.sender_constraint.dpop.failed',
                                           occurredAt: new DateTimeImmutable(),
                                           context   : [
                                                           'reason' => $reason,
                                                           'method' => strtoupper($input->method),
                                                           'uri'    => $input->uri,
                                                       ]
                                       ));
    }

    private function sameUri(string $expected, string $actual) : bool
    {
        return rtrim($expected, '/') === rtrim($actual, '/');
    }
}
