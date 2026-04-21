<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use SensitiveParameter;

final readonly class DetectUnsafeDeploymentMode
{
    /**
     * @param list<string> $trustedProxies
     *
     * @return list<string>
     */
    public function execute(
        HttpOAuthProofInput $input,
        bool|null           $production = null,
        bool|null           $senderConstraintExpected = null,
        array               $trustedProxies = []
    ) : array
    {
        $production               ??= true;
        $senderConstraintExpected ??= false;
        $warnings                 = [];
        $scheme                   = strtolower((string) parse_url($input->uri, PHP_URL_SCHEME));
        $forwardedProto           = $this->readHeader(headers: $input->headers, name: 'X-Forwarded-Proto');
        $remoteAddress            = $this->readServerValue(server: $input->server, name: 'REMOTE_ADDR');

        if ($production && $scheme !== 'https' && strtolower((string) $forwardedProto) !== 'https') {
            $warnings[] = 'plain_http_in_production';
        }

        if ($senderConstraintExpected) {
            if ($trustedProxies === [] && $this->hasProxyHeaders(headers: $input->headers)) {
                $warnings[] = 'trusted_proxy_contract_missing';
            }

            if ($this->readHeader(headers: $input->headers, name: 'DPoP') === null && $this->readServerValue(server: $input->server, name: 'TLS_CLIENT_CERT_SHA256') === null) {
                $warnings[] = 'sender_constraint_signal_missing';
            }

            if ($remoteAddress !== null && $this->hasProxyHeaders(headers: $input->headers) && ! in_array($remoteAddress, $trustedProxies, true)) {
                $warnings[] = 'untrusted_proxy_source';
            }
        }

        return array_values(array_unique($warnings));
    }

    /**
     * @param array<string, mixed> $headers
     */
    private function readHeader(#[SensitiveParameter] array $headers, string $name) : string|null
    {
        foreach ($headers as $header => $value) {
            if (strcasecmp($header, $name) !== 0) {
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

    /**
     * @param array<string, mixed> $headers
     */
    private function hasProxyHeaders(#[SensitiveParameter] array $headers) : bool
    {
        foreach (array_keys($headers) as $header) {
            $normalized = strtolower($header);

            if (str_starts_with($normalized, 'x-forwarded-') || str_starts_with($normalized, 'x-client-cert') || str_starts_with($normalized, 'x-tls-client-')) {
                return true;
            }
        }

        return false;
    }
}
