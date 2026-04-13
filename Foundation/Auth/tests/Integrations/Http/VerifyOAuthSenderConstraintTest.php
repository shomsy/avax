<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Http;

use Avax\Auth\Integrations\Http\DpopProofFailed;
use Avax\Auth\Integrations\Http\HttpOAuthProofInput;
use Avax\Auth\Integrations\Http\MtlsBindingFailed;
use Avax\Auth\Integrations\Http\SenderConstraintVerificationFailed;
use Avax\Auth\Integrations\Http\VerifyDpopProof;
use Avax\Auth\Integrations\Http\VerifyMtlsSenderConstraint;
use Avax\Auth\Integrations\Http\VerifyOAuthSenderConstraint;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\InMemoryDpopProofReplayStore;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class VerifyOAuthSenderConstraintTest extends TestCase
{
    public function testBearerPolicyDoesNotRequireConstraintProof() : void
    {
        $auditLog = new InMemoryAuditLog();
        $verifier = new VerifyOAuthSenderConstraint(
            verifyDpopProof: new VerifyDpopProof(
                codec      : new HmacTokenCodec('proof-secret'),
                replayStore: new InMemoryDpopProofReplayStore(),
                auditLog   : $auditLog
            ),
            verifyMtlsSenderConstraint: new VerifyMtlsSenderConstraint($auditLog),
            auditLog                  : $auditLog
        );

        $binding = $verifier->execute(new HttpOAuthProofInput(
            method: 'GET',
            uri   : 'https://api.example.test/me'
        ));

        $this->assertNull($binding);
        $this->assertSame([], $auditLog->events());
    }

    public function testDpopReplayIsRejectedAndAudited() : void
    {
        $auditLog = new InMemoryAuditLog();
        $codec = new HmacTokenCodec('proof-secret', keyId: 'dpop-2026-04');
        $proof = $codec->encode([
            'jti' => 'proof-1',
            'iat' => time(),
            'htu' => 'https://api.example.test/me',
            'htm' => 'GET',
            'jkt' => 'thumb-1',
            'ath' => $this->hashAccessToken('access-token'),
        ]);
        $verifier = new VerifyOAuthSenderConstraint(
            verifyDpopProof: new VerifyDpopProof(
                codec      : $codec,
                replayStore: new InMemoryDpopProofReplayStore(),
                auditLog   : $auditLog
            ),
            verifyMtlsSenderConstraint: new VerifyMtlsSenderConstraint($auditLog),
            auditLog                  : $auditLog
        );
        $input = new HttpOAuthProofInput(
            method     : 'GET',
            uri        : 'https://api.example.test/me',
            headers    : ['DPoP' => $proof],
            accessToken: 'access-token'
        );

        $binding = $verifier->execute(
            input                   : $input,
            requiredSenderConstraint: OAuthSenderConstraintType::DPOP
        );

        $this->assertSame('thumb-1', $binding?->thumbprint);

        $this->expectException(DpopProofFailed::class);
        $this->expectExceptionMessage('replay_detected');

        $verifier->execute(
            input                   : $input,
            requiredSenderConstraint: OAuthSenderConstraintType::DPOP
        );
    }

    public function testDpopProofMismatchAndKeyRotationAreRejected() : void
    {
        $auditLog = new InMemoryAuditLog();
        $oldCodec = new HmacTokenCodec('proof-secret', keyId: 'dpop-2026-04');
        $newCodec = new HmacTokenCodec('proof-secret', keyId: 'dpop-2026-05');
        $wrongMethodProof = $oldCodec->encode([
            'jti' => 'proof-2',
            'iat' => time(),
            'htu' => 'https://api.example.test/me',
            'htm' => 'POST',
            'jkt' => 'thumb-1',
            'ath' => $this->hashAccessToken('access-token'),
        ]);
        $wrongAthProof = $oldCodec->encode([
            'jti' => 'proof-3',
            'iat' => time(),
            'htu' => 'https://api.example.test/me',
            'htm' => 'GET',
            'jkt' => 'thumb-1',
            'ath' => $this->hashAccessToken('different-token'),
        ]);

        $verifier = new VerifyDpopProof(
            codec      : $oldCodec,
            replayStore: new InMemoryDpopProofReplayStore(),
            auditLog   : $auditLog
        );

        try {
            $verifier->execute(new HttpOAuthProofInput(
                method     : 'GET',
                uri        : 'https://api.example.test/me',
                headers    : ['DPoP' => $wrongMethodProof],
                accessToken: 'access-token'
            ));
            $this->fail('Expected method mismatch.');
        } catch (DpopProofFailed $exception) {
            $this->assertStringContainsString('method_mismatch', $exception->getMessage());
        }

        try {
            $verifier->execute(new HttpOAuthProofInput(
                method     : 'GET',
                uri        : 'https://api.example.test/me',
                headers    : ['DPoP' => $wrongAthProof],
                accessToken: 'access-token'
            ));
            $this->fail('Expected access token mismatch.');
        } catch (DpopProofFailed $exception) {
            $this->assertStringContainsString('access_token_mismatch', $exception->getMessage());
        }

        $rotatedVerifier = new VerifyDpopProof(
            codec      : $newCodec,
            replayStore: new InMemoryDpopProofReplayStore(),
            auditLog   : $auditLog
        );
        $validOldProof = $oldCodec->encode([
            'jti' => 'proof-4',
            'iat' => time(),
            'htu' => 'https://api.example.test/me',
            'htm' => 'GET',
            'jkt' => 'thumb-1',
            'ath' => $this->hashAccessToken('access-token'),
        ]);

        $this->expectException(DpopProofFailed::class);
        $this->expectExceptionMessage('decode_failed');

        $rotatedVerifier->execute(new HttpOAuthProofInput(
            method     : 'GET',
            uri        : 'https://api.example.test/me',
            headers    : ['DPoP' => $validOldProof],
            accessToken: 'access-token'
        ));
    }

    public function testSenderConstraintMismatchAndMtlsPolicyAreRejected() : void
    {
        $auditLog = new InMemoryAuditLog();
        $codec = new HmacTokenCodec('proof-secret');
        $proof = $codec->encode([
            'jti' => 'proof-5',
            'iat' => time(),
            'htu' => 'https://api.example.test/me',
            'htm' => 'GET',
            'jkt' => 'thumb-actual',
            'ath' => $this->hashAccessToken('access-token'),
        ]);
        $verifier = new VerifyOAuthSenderConstraint(
            verifyDpopProof: new VerifyDpopProof(
                codec      : $codec,
                replayStore: new InMemoryDpopProofReplayStore(),
                auditLog   : $auditLog
            ),
            verifyMtlsSenderConstraint: new VerifyMtlsSenderConstraint($auditLog),
            auditLog                  : $auditLog
        );

        try {
            $verifier->execute(
                input                    : new HttpOAuthProofInput(
                    method     : 'GET',
                    uri        : 'https://api.example.test/me',
                    headers    : ['DPoP' => $proof],
                    accessToken: 'access-token'
                ),
                expectedSenderConstraint : new OAuthSenderConstraint(
                    type      : OAuthSenderConstraintType::DPOP,
                    thumbprint: 'thumb-expected'
                )
            );
            $this->fail('Expected sender-constraint mismatch.');
        } catch (SenderConstraintVerificationFailed $exception) {
            $this->assertSame('OAuth sender constraint does not match the bound token.', $exception->getMessage());
        }

        $this->assertContains(
            'auth.oauth.sender_constraint.mismatch',
            array_map(static fn ($event) => $event->name, $auditLog->events())
        );

        $mtlsBinding = $verifier->execute(
            input                    : new HttpOAuthProofInput(
                method : 'GET',
                uri    : 'https://api.example.test/me',
                server : ['TLS_CLIENT_CERT_SHA256' => 'mtls-thumb-1']
            ),
            expectedSenderConstraint : new OAuthSenderConstraint(
                type      : OAuthSenderConstraintType::MTLS,
                thumbprint: 'mtls-thumb-1'
            )
        );

        $this->assertSame(OAuthSenderConstraintType::MTLS, $mtlsBinding?->type);

        $this->expectException(MtlsBindingFailed::class);
        $this->expectExceptionMessage('does not match');

        $verifier->execute(
            input                    : new HttpOAuthProofInput(
                method : 'GET',
                uri    : 'https://api.example.test/me',
                server : ['TLS_CLIENT_CERT_SHA256' => 'mtls-thumb-2']
            ),
            expectedSenderConstraint : new OAuthSenderConstraint(
                type      : OAuthSenderConstraintType::MTLS,
                thumbprint: 'mtls-thumb-1'
            )
        );
    }

    private function hashAccessToken(string $token) : string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $token, true)), '+/', '-_'), '=');
    }
}
