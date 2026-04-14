<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

final readonly class VerifyTrustedProxyHeaders
{
    /**
     * @param list<string> $trustedProxies
     * @param list<string> $forwardedHeaders
     * @param list<string> $proxyOnlyClientCertificateHeaders
     */
    public function __construct(
        private array $trustedProxies = [],
        private array $forwardedHeaders = ['X-Forwarded-For', 'X-Forwarded-Proto', 'X-Forwarded-Host'],
        private array $proxyOnlyClientCertificateHeaders = ['X-Client-Cert', 'X-Client-Cert-Fingerprint', 'X-TLS-Client-Cert-Verify', 'X-TLS-Client-Cert-SHA256']
    ) {}

    /**
     * @throws TrustedProxyViolation
     */
    public function execute(HttpOAuthProofInput $input) : void
    {
        $remoteAddress = $this->readServerValue(server: $input->server, name: 'REMOTE_ADDR');

        if ($remoteAddress !== null && in_array($remoteAddress, $this->trustedProxies, true)) {
            return;
        }

        foreach ($input->headers as $header => $value) {
            if ($value === null) {
                continue;
            }

            $headerName = $header;

            if ($this->matchesProxyOnlyCertificateHeader(header: $headerName)) {
                throw TrustedProxyViolation::untrustedClientCertificateMetadata(header: $headerName);
            }

            if ($this->matchesForwardedHeader(header: $headerName)) {
                throw TrustedProxyViolation::untrustedForwardedHeader(header: $headerName);
            }
        }
    }

    private function matchesForwardedHeader(string $header) : bool
    {
        foreach ($this->forwardedHeaders as $candidate) {
            if (strcasecmp($header, $candidate) === 0) {
                return true;
            }
        }

        return false;
    }

    private function matchesProxyOnlyCertificateHeader(string $header) : bool
    {
        foreach ($this->proxyOnlyClientCertificateHeaders as $candidate) {
            if (strcasecmp($header, $candidate) === 0) {
                return true;
            }
        }

        return str_starts_with(strtolower($header), 'x-tls-client-');
    }

    /**
     * @param array<string, mixed> $server
     */
    private function readServerValue(array $server, string $name) : string|null
    {
        $value = $server[$name] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }
}
