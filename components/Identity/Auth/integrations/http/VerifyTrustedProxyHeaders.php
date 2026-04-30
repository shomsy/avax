<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Http;

use SensitiveParameter;

final readonly class VerifyTrustedProxyHeaders
{
    /** @var list<string> */
    private array $forwardedHeaders;
    /** @var list<string> */
    private array $trustedProxies;

    /**
     * @param list<string> $trustedProxies
     * @param list<string> $forwardedHeaders
     * @param list<string> $proxyOnlyClientCertificateHeaders
     */
    public function __construct(
        array         $trustedProxies = null,
        #[SensitiveParameter]
        array         $forwardedHeaders = null,
        #[SensitiveParameter]
        private array $proxyOnlyClientCertificateHeaders = ['X-Client-Cert', 'X-Client-Cert-Fingerprint', 'X-TLS-Client-Cert-Verify', 'X-TLS-Client-Cert-SHA256'],
    )
    {
        $trustedProxies   ??= [];
        $forwardedHeaders ??= ['X-Forwarded-For', 'X-Forwarded-Proto', 'X-Forwarded-Host'];
        $this->trustedProxies   = $trustedProxies;
        $this->forwardedHeaders = $forwardedHeaders;
    }

    /**
     * @throws TrustedProxyViolation
     */
    public function execute(HttpOAuthProofInput $input) : void
    {
        $remoteAddress = $this->readServerValue(server: $input->server);

        if ($remoteAddress !== null && in_array(needle: $remoteAddress, haystack: $this->trustedProxies, strict: true)) {
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

    /**
     * @param array<string, mixed> $server
     */
    private function readServerValue(array $server) : string|null
    {
        $value = $server['REMOTE_ADDR'] ?? null;

        return is_scalar(value: $value) ? (string) $value : null;
    }

    private function matchesProxyOnlyCertificateHeader(#[SensitiveParameter] string $header) : bool
    {
        foreach ($this->proxyOnlyClientCertificateHeaders as $candidate) {
            if (strcasecmp(string1: $header, string2: $candidate) === 0) {
                return true;
            }
        }

        return str_starts_with(haystack: strtolower(string: $header), needle: 'x-tls-client-');
    }

    private function matchesForwardedHeader(
        #[SensitiveParameter]
        string $header,
    ) : bool
    {
        return array_any(array: $this->forwardedHeaders, callback: static fn ($candidate) => strcasecmp(string1: $header, string2: $candidate) === 0);
    }
}
