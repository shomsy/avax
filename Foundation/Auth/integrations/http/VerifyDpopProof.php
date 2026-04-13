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
use DateTimeImmutable;

/**
 * Verifies a DPoP-like proof and converts it into a sender constraint binding.
 */
final readonly class VerifyDpopProof
{
    public function __construct(
        private TokenCodecInterface $codec,
        private DpopProofReplayStoreInterface $replayStore,
        private AuditLogInterface $auditLog = new NullAuditLog(),
        private int $maxAgeSeconds = 300
    ) {}

    /**
     * @throws DpopProofFailed
     */
    public function execute(HttpOAuthProofInput $input) : OAuthSenderConstraint
    {
        $proof = $this->readHeader($input->headers, 'DPoP')
            ?? $this->readServerValue($input->server, 'HTTP_DPOP');

        if ($proof === null || $proof === '') {
            $this->recordFailure($input, 'missing_proof');
            throw DpopProofFailed::missing();
        }

        $claims = $this->codec->decode($proof);

        if (! is_array($claims)) {
            $this->recordFailure($input, 'decode_failed');
            throw DpopProofFailed::invalid('decode_failed');
        }

        $jti = $claims['jti'] ?? null;
        $iat = $claims['iat'] ?? null;
        $htu = $claims['htu'] ?? null;
        $htm = $claims['htm'] ?? null;
        $thumbprint = $claims['jkt'] ?? null;
        $ath = $claims['ath'] ?? null;

        if (
            ! is_string($jti)
            || ! is_int($iat)
            || ! is_string($htu)
            || ! is_string($htm)
            || ! is_string($thumbprint)
        ) {
            $this->recordFailure($input, 'missing_required_claims');
            throw DpopProofFailed::invalid('missing_required_claims');
        }

        $now = new DateTimeImmutable();
        $issuedAt = new DateTimeImmutable("@{$iat}");
        $age = abs($now->getTimestamp() - $issuedAt->getTimestamp());

        if ($age > $this->maxAgeSeconds) {
            $this->recordFailure($input, 'proof_expired');
            throw DpopProofFailed::invalid('proof_expired');
        }

        if (strtoupper($htm) !== strtoupper($input->method)) {
            $this->recordFailure($input, 'method_mismatch');
            throw DpopProofFailed::invalid('method_mismatch');
        }

        if (! $this->sameUri($htu, $input->uri)) {
            $this->recordFailure($input, 'uri_mismatch');
            throw DpopProofFailed::invalid('uri_mismatch');
        }

        if ($input->accessToken !== null) {
            $expectedAth = rtrim(strtr(base64_encode(hash('sha256', $input->accessToken, true)), '+/', '-_'), '=');

            if (! is_string($ath) || ! hash_equals($expectedAth, $ath)) {
                $this->recordFailure($input, 'access_token_mismatch');
                throw DpopProofFailed::invalid('access_token_mismatch');
            }
        }

        $proofId = hash('sha256', "{$thumbprint}:{$jti}");

        if (! $this->replayStore->remember($proofId, $issuedAt->modify("+{$this->maxAgeSeconds} seconds"))) {
            $this->recordFailure($input, 'replay_detected');
            throw DpopProofFailed::invalid('replay_detected');
        }

        return new OAuthSenderConstraint(
            type      : OAuthSenderConstraintType::DPOP,
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

    private function sameUri(string $expected, string $actual) : bool
    {
        return rtrim($expected, '/') === rtrim($actual, '/');
    }

    private function recordFailure(HttpOAuthProofInput $input, string $reason) : void
    {
        $this->auditLog->record(new AuditEvent(
            name      : 'auth.oauth.sender_constraint.dpop.failed',
            occurredAt: new DateTimeImmutable(),
            context   : [
                'reason' => $reason,
                'method' => strtoupper($input->method),
                'uri' => $input->uri,
            ]
        ));
    }
}
