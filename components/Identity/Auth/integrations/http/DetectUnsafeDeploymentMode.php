<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Http;

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
        bool  $production = null,
        bool  $senderConstraintExpected = null,
        array $trustedProxies = [],
    ) : array
    {
        $production               ??= true;
        $senderConstraintExpected ??= false;
        $warnings = [];
        $scheme = strtolower(string: (string) parse_url(url: $input->uri, component: PHP_URL_SCHEME));
        $forwardedProto = $this->readHeader(headers: $input->headers, name: 'X-Forwarded-Proto');
        $remoteAddress = $this->readServerValue(server: $input->server, name: 'REMOTE_ADDR');

        if ($production && $scheme !== 'https' && strtolower(string: (string) $forwardedProto) !== 'https') {
            $warnings[] = 'plain_http_in_production';
        }

        if ($senderConstraintExpected) {
            if ($trustedProxies === [] && $this->hasProxyHeaders(headers: $input->headers)) {
                $warnings[] = 'trusted_proxy_contract_missing';
            }

            if ($this->readHeader(headers: $input->headers, name: 'DPoP') === null && $this->readServerValue(server: $input->server, name: 'TLS_CLIENT_CERT_SHA256') === null) {
                $warnings[] = 'sender_constraint_signal_missing';
            }

            if ($remoteAddress !== null && $this->hasProxyHeaders(headers: $input->headers) && ! in_array(needle: $remoteAddress, haystack: $trustedProxies, strict: true)) {
                $warnings[] = 'untrusted_proxy_source';
            }
        }

        return array_values(array: array_unique(array: $warnings));
    }

    /**
     * @param array<string, mixed> $headers
     */
    private function readHeader(#[SensitiveParameter] array $headers, string $name) : string|null
    {
        foreach ($headers as $header => $value) {
            if (strcasecmp(string1: $header, string2: $name) !== 0) {
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
    private function readServerValue(array $server, string $name) : string|null
    {
        $value = $server[$name] ?? null;

        return is_scalar(value: $value) ? (string) $value : null;
    }

    /**
     * @param array<string, mixed> $headers
     */
    private function hasProxyHeaders(#[SensitiveParameter] array $headers) : bool
    {
        foreach (array_keys(array: $headers) as $header) {
            $normalized = strtolower(string: $header);

            if (str_starts_with(haystack: $normalized, needle: 'x-forwarded-') || str_starts_with(haystack: $normalized, needle: 'x-client-cert') || str_starts_with(haystack: $normalized, needle: 'x-tls-client-')) {
                return true;
            }
        }

        return false;
    }
}
