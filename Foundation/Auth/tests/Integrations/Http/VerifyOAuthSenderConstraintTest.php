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
use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\InMemoryDpopProofReplayStore;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\HmacTokenCodec;
use DateMalformedStringException;
use PHPUnit\Framework\TestCase;
use SensitiveParameter;

final class VerifyOAuthSenderConstraintTest extends TestCase
{
    /**
     * @throws DateMalformedStringException
     */
    public function testBearerPolicyDoesNotRequireConstraintProof() : void
    {
        $auditLog = new InMemoryAuditLog();
        $verifier = new VerifyOAuthSenderConstraint(
            verifyDpopProof           : new VerifyDpopProof(
                                            codec      : new HmacTokenCodec(secret: 'proof-secret'),
                                            replayStore: new InMemoryDpopProofReplayStore(),
                                            auditLog   : $auditLog
                                        ),
            verifyMtlsSenderConstraint: new VerifyMtlsSenderConstraint(auditLog: $auditLog),
            auditLog                  : $auditLog
        );

        $binding = $verifier->execute(input: new HttpOAuthProofInput(
                                                 method: 'GET',
                                                 uri   : 'https://api.example.test/me'
                                             ));

        $this->assertNull(actual: $binding);
        $this->assertSame(expected: [], actual: $auditLog->events());
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testDpopReplayIsRejectedAndAudited() : void
    {
        $auditLog = new InMemoryAuditLog();
        $codec    = new HmacTokenCodec(secret: 'proof-secret', keyId: 'dpop-2026-04');
        $proof    = $codec->encode(claims: [
                                               'jti' => 'proof-1',
                                               'iat' => time(),
                                               'htu' => 'https://api.example.test/me',
                                               'htm' => 'GET',
                                               'jkt' => 'thumb-1',
                                               'ath' => $this->hashAccessToken(token: 'access-token'),
                                           ]);
        $verifier = new VerifyOAuthSenderConstraint(
            verifyDpopProof           : new VerifyDpopProof(
                                            codec      : $codec,
                                            replayStore: new InMemoryDpopProofReplayStore(),
                                            auditLog   : $auditLog
                                        ),
            verifyMtlsSenderConstraint: new VerifyMtlsSenderConstraint(auditLog: $auditLog),
            auditLog                  : $auditLog
        );
        $input    = new HttpOAuthProofInput(
            method     : 'GET',
            uri        : 'https://api.example.test/me',
            headers    : ['DPoP' => $proof],
            accessToken: 'access-token'
        );

        $binding = $verifier->execute(
            input                   : $input,
            requiredSenderConstraint: OAuthSenderConstraintType::DPOP
        );

        $this->assertSame(expected: 'thumb-1', actual: $binding?->thumbprint);

        $this->expectException(DpopProofFailed::class);
        $this->expectExceptionMessage('replay_detected');

        $verifier->execute(
            input                   : $input,
            requiredSenderConstraint: OAuthSenderConstraintType::DPOP
        );
    }

    private function hashAccessToken(#[SensitiveParameter] string $token) : string
    {
        return hash('sha256', $token, true)
                |> base64_encode(...)
                |> (static fn ($x) => strtr($x, '+/', '-_'))
                |> (static fn ($x) => rtrim($x, '='));
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testDpopProofMismatchAndKeyRotationAreRejected() : void
    {
        $auditLog         = new InMemoryAuditLog();
        $oldCodec         = new HmacTokenCodec(secret: 'proof-secret', keyId: 'dpop-2026-04');
        $newCodec         = new HmacTokenCodec(secret: 'proof-secret', keyId: 'dpop-2026-05');
        $wrongMethodProof = $oldCodec->encode(claims: [
                                                          'jti' => 'proof-2',
                                                          'iat' => time(),
                                                          'htu' => 'https://api.example.test/me',
                                                          'htm' => 'POST',
                                                          'jkt' => 'thumb-1',
                                                          'ath' => $this->hashAccessToken(token: 'access-token'),
                                                      ]);
        $wrongAthProof    = $oldCodec->encode(claims: [
                                                          'jti' => 'proof-3',
                                                          'iat' => time(),
                                                          'htu' => 'https://api.example.test/me',
                                                          'htm' => 'GET',
                                                          'jkt' => 'thumb-1',
                                                          'ath' => $this->hashAccessToken(token: 'different-token'),
                                                      ]);
        $wrongUriProof    = $oldCodec->encode(claims: [
                                                          'jti' => 'proof-3b',
                                                          'iat' => time(),
                                                          'htu' => 'https://api.example.test/admin',
                                                          'htm' => 'GET',
                                                          'jkt' => 'thumb-1',
                                                          'ath' => $this->hashAccessToken(token: 'access-token'),
                                                      ]);

        $verifier = new VerifyDpopProof(
            codec      : $oldCodec,
            replayStore: new InMemoryDpopProofReplayStore(),
            auditLog   : $auditLog
        );

        try {
            $verifier->execute(input: new HttpOAuthProofInput(
                                          method     : 'GET',
                                          uri        : 'https://api.example.test/me',
                                          headers    : ['DPoP' => $wrongMethodProof],
                                          accessToken: 'access-token'
                                      ));
            $this->fail(message: 'Expected method mismatch.');
        } catch (DpopProofFailed $exception) {
            $this->assertStringContainsString(needle: 'method_mismatch', haystack: $exception->getMessage());
        }

        try {
            $verifier->execute(input: new HttpOAuthProofInput(
                                          method     : 'GET',
                                          uri        : 'https://api.example.test/me',
                                          headers    : ['DPoP' => $wrongAthProof],
                                          accessToken: 'access-token'
                                      ));
            $this->fail(message: 'Expected access token mismatch.');
        } catch (DpopProofFailed $exception) {
            $this->assertStringContainsString(needle: 'access_token_mismatch', haystack: $exception->getMessage());
        }

        try {
            $verifier->execute(input: new HttpOAuthProofInput(
                                          method     : 'GET',
                                          uri        : 'https://api.example.test/me',
                                          headers    : ['DPoP' => $wrongUriProof],
                                          accessToken: 'access-token'
                                      ));
            $this->fail(message: 'Expected URI mismatch.');
        } catch (DpopProofFailed $exception) {
            $this->assertStringContainsString(needle: 'uri_mismatch', haystack: $exception->getMessage());
        }

        $rotatedVerifier = new VerifyDpopProof(
            codec      : $newCodec,
            replayStore: new InMemoryDpopProofReplayStore(),
            auditLog   : $auditLog
        );
        $validOldProof   = $oldCodec->encode(claims: [
                                                         'jti' => 'proof-4',
                                                         'iat' => time(),
                                                         'htu' => 'https://api.example.test/me',
                                                         'htm' => 'GET',
                                                         'jkt' => 'thumb-1',
                                                         'ath' => $this->hashAccessToken(token: 'access-token'),
                                                     ]);

        $this->expectException(DpopProofFailed::class);
        $this->expectExceptionMessage('decode_failed');

        $rotatedVerifier->execute(input: new HttpOAuthProofInput(
                                             method     : 'GET',
                                             uri        : 'https://api.example.test/me',
                                             headers    : ['DPoP' => $validOldProof],
                                             accessToken: 'access-token'
                                         ));
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testSenderConstraintMismatchAndMtlsPolicyAreRejected() : void
    {
        $auditLog = new InMemoryAuditLog();
        $codec    = new HmacTokenCodec(secret: 'proof-secret');
        $proof    = $codec->encode(claims: [
                                               'jti' => 'proof-5',
                                               'iat' => time(),
                                               'htu' => 'https://api.example.test/me',
                                               'htm' => 'GET',
                                               'jkt' => 'thumb-actual',
                                               'ath' => $this->hashAccessToken(token: 'access-token'),
                                           ]);
        $verifier = new VerifyOAuthSenderConstraint(
            verifyDpopProof           : new VerifyDpopProof(
                                            codec      : $codec,
                                            replayStore: new InMemoryDpopProofReplayStore(),
                                            auditLog   : $auditLog
                                        ),
            verifyMtlsSenderConstraint: new VerifyMtlsSenderConstraint(auditLog: $auditLog),
            auditLog                  : $auditLog
        );

        try {
            $verifier->execute(
                input                   : new HttpOAuthProofInput(
                                              method     : 'GET',
                                              uri        : 'https://api.example.test/me',
                                              headers    : ['DPoP' => $proof],
                                              accessToken: 'access-token'
                                          ),
                expectedSenderConstraint: new OAuthSenderConstraint(
                                              type      : OAuthSenderConstraintType::DPOP,
                                              thumbprint: 'thumb-expected'
                                          )
            );
            $this->fail(message: 'Expected sender-constraint mismatch.');
        } catch (SenderConstraintVerificationFailed $exception) {
            $this->assertSame(expected: 'OAuth sender constraint does not match the bound token.', actual: $exception->getMessage());
        }

        $this->assertContains(
            needle  : 'auth.oauth.sender_constraint.mismatch',
            haystack: array_map(static fn ($event) => $event->name, $auditLog->events())
        );

        $mtlsBinding = $verifier->execute(
            input                   : new HttpOAuthProofInput(
                                          method: 'GET',
                                          uri   : 'https://api.example.test/me',
                                          server: ['TLS_CLIENT_CERT_SHA256' => 'mtls-thumb-1']
                                      ),
            expectedSenderConstraint: new OAuthSenderConstraint(
                                          type      : OAuthSenderConstraintType::MTLS,
                                          thumbprint: 'mtls-thumb-1'
                                      )
        );

        $this->assertSame(expected: OAuthSenderConstraintType::MTLS, actual: $mtlsBinding?->type);

        $this->expectException(MtlsBindingFailed::class);
        $this->expectExceptionMessage('does not match');

        $verifier->execute(
            input                   : new HttpOAuthProofInput(
                                          method: 'GET',
                                          uri   : 'https://api.example.test/me',
                                          server: ['TLS_CLIENT_CERT_SHA256' => 'mtls-thumb-2']
                                      ),
            expectedSenderConstraint: new OAuthSenderConstraint(
                                          type      : OAuthSenderConstraintType::MTLS,
                                          thumbprint: 'mtls-thumb-1'
                                      )
        );
    }
}
