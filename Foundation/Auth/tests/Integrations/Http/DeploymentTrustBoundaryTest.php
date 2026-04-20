<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Http;

use Avax\Auth\Integrations\Http\DetectUnsafeDeploymentMode;
use Avax\Auth\Integrations\Http\HttpOAuthProofInput;
use Avax\Auth\Integrations\Http\TrustedProxyViolation;
use Avax\Auth\Integrations\Http\VerifyDpopProof;
use Avax\Auth\Integrations\Http\VerifyMtlsSenderConstraint;
use Avax\Auth\Integrations\Http\VerifyOAuthSenderConstraint;
use Avax\Auth\Integrations\Http\VerifyTrustedProxyHeaders;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\InMemoryDpopProofReplayStore;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\HmacTokenCodec;
use PHPUnit\Framework\TestCase;

final class DeploymentTrustBoundaryTest extends TestCase
{
    public function testUntrustedForwardedHeadersAreRejected() : void
    {
        $verifier = new VerifyTrustedProxyHeaders(trustedProxies: ['10.0.0.10']);

        $this->expectException(TrustedProxyViolation::class);
        $this->expectExceptionMessage('Untrusted forwarded header detected: X-Forwarded-For');

        $verifier->execute(input: new HttpOAuthProofInput(
                                      method : 'GET',
                                      uri    : 'https://api.example.test/me',
                                      headers: ['X-Forwarded-For' => '198.51.100.10'],
                                      server : ['REMOTE_ADDR' => '203.0.113.20']
                                  ));
    }

    public function testUntrustedClientCertificateMetadataIsRejected() : void
    {
        $verifier = new VerifyTrustedProxyHeaders(trustedProxies: ['10.0.0.10']);

        $this->expectException(TrustedProxyViolation::class);
        $this->expectExceptionMessage('Untrusted client-certificate metadata detected: X-Client-Cert');

        $verifier->execute(input: new HttpOAuthProofInput(
                                      method : 'GET',
                                      uri    : 'https://api.example.test/me',
                                      headers: ['X-Client-Cert' => 'pem-data'],
                                      server : ['REMOTE_ADDR' => '203.0.113.20']
                                  ));
    }

    public function testUnsafeDeploymentWarningsAreReported() : void
    {
        $warnings = (new DetectUnsafeDeploymentMode())->execute(
            input                   : new HttpOAuthProofInput(
                                          method : 'POST',
                                          uri    : 'http://api.example.test/token',
                                          headers: ['X-Forwarded-For' => '198.51.100.10'],
                                          server : ['REMOTE_ADDR' => '203.0.113.20']
                                      ),
            production              : true,
            senderConstraintExpected: true
        );

        $this->assertSame(
            expected: ['plain_http_in_production', 'trusted_proxy_contract_missing', 'sender_constraint_signal_missing', 'untrusted_proxy_source'],
            actual  : $warnings
        );
    }

    public function testTrustedProxyAndSenderConstraintCanPassTogether() : void
    {
        $auditLog       = new InMemoryAuditLog();
        $codec          = new HmacTokenCodec(secret: 'proof-secret');
        $proof          = $codec->encode(claims: [
                                                     'jti' => 'deployment-proof-1',
                                                     'iat' => time(),
                                                     'htu' => 'https://api.example.test/me',
                                                     'htm' => 'GET',
                                                     'jkt' => 'thumb-1',
                                                     'ath' => hash('sha256', 'access-token', true)
                                                             |> base64_encode(...)
                                                             |> (static fn ($x) => strtr($x, '+/', '-_'))
                                                             |> (static fn ($x) => rtrim($x, '=')),
                                                 ]);
        $proxyVerifier  = new VerifyTrustedProxyHeaders(trustedProxies: ['10.0.0.10']);
        $senderVerifier = new VerifyOAuthSenderConstraint(
            verifyDpopProof           : new VerifyDpopProof(
                                            codec      : $codec,
                                            replayStore: new InMemoryDpopProofReplayStore(),
                                            auditLog   : $auditLog
                                        ),
            verifyMtlsSenderConstraint: new VerifyMtlsSenderConstraint(auditLog: $auditLog),
            auditLog                  : $auditLog
        );
        $input          = new HttpOAuthProofInput(
            method     : 'GET',
            uri        : 'https://api.example.test/me',
            headers    : [
                             'X-Forwarded-For' => '198.51.100.10',
                             'DPoP'            => $proof,
                         ],
            server     : ['REMOTE_ADDR' => '10.0.0.10'],
            accessToken: 'access-token'
        );

        $proxyVerifier->execute(input: $input);
        $binding = $senderVerifier->execute(
            input                   : $input,
            expectedSenderConstraint: new OAuthSenderConstraint(
                                          type      : OAuthSenderConstraintType::DPOP,
                                          thumbprint: 'thumb-1'
                                      )
        );

        $this->assertSame(expected: OAuthSenderConstraintType::DPOP, actual: $binding?->type);
    }
}
