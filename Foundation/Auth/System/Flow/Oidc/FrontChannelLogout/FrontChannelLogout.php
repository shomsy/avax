<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\FrontChannelLogout;

use Avax\Auth\System\Foundation\Clock;

/**
 * Generates front-channel logout URLs for relying parties that support OIDC Front-Channel Logout.
 *
 * This allows RPs to receive logout notifications via browser redirects
 * instead of back-channel callbacks.
 */
final readonly class FrontChannelLogout
{
    public function __construct(
        private Clock $clock
    ) {}

    /**
     * @param list<string> $registeredLogoutUris
     */
    public function buildLogoutUrl(
        string $clientId,
        string $idTokenHint,
        array $registeredLogoutUris,
        string $postLogoutRedirectUri = null
    ) : string {
        $baseUri = $registeredLogoutUris[0] ?? null;

        if ($baseUri === null) {
            return '';
        }

        $params = [
            'id_token_hint' => $idTokenHint,
            'client_id'    => $clientId,
            'logout_uri'    => implode(',', $registeredLogoutUris),
        ];

        if ($postLogoutRedirectUri !== null) {
            $params['post_logout_redirect_uri'] = $postLogoutRedirectUri;
        }

        $separator = str_contains($baseUri, '?') ? '&' : '?';

        return $baseUri . $separator . http_build_query($params);
    }

    /**
     * Parses front-channel logout request parameters.
     */
    public function parseRequest(string $uri) : FrontChannelLogoutRequest
    {
        $parsed = parse_url($uri);
        parse_str($parsed['query'] ?? '', $params);

        return new FrontChannelLogoutRequest(
            idTokenHint        : $params['id_token_hint'] ?? null,
            clientId          : $params['client_id'] ?? null,
            logoutUri         : $params['logout_uri'] ?? null,
            postLogoutRedirectUri : $params['post_logout_redirect_uri'] ?? null,
            state            : $params['state'] ?? null
        );
    }

    /**
     * Registers logout URI for a client.
     *
     * @param list<string> $logoutUris
     * @return list<string>
     */
    public function filterValidLogoutUris(array $logoutUris) : array
    {
        return array_values(array_filter($logoutUris, fn(string $uri) => $this->isValidLogoutUri($uri)));
    }

    private function isValidLogoutUri(string $uri) : bool
    {
        $parsed = parse_url($uri);

        return isset($parsed['scheme']) && in_array(strtolower($parsed['scheme']), ['https', 'http'], true)
            && isset($parsed['host']);
    }
}