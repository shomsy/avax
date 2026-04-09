<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use Avax\Auth\Integrations\Cookies\ResolveSessionAllowance;
use Avax\Auth\Integrations\Headers\ReadBearerToken;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationRequest;

/**
 * Maps HTTP transport input into the kernel auth ingress request.
 */
final readonly class MapAuthenticationRequest
{
    public function __construct(
        private ReadBearerToken         $readBearerToken = new ReadBearerToken(),
        private ResolveSessionAllowance $resolveSessionAllowance = new ResolveSessionAllowance()
    ) {}

    public function execute(HttpAuthenticationInput $input) : AuthenticationRequest
    {
        return new AuthenticationRequest(
            bearerToken : $this->readBearerToken->execute(headers: $input->headers, server: $input->server),
            allowSession: $this->resolveSessionAllowance->execute(
                              cookies          : $input->cookies,
                              allowSession     : $input->allowSession,
                              sessionCookieName: $input->sessionCookieName
                          ),
            ipAddress   : $this->readServerValue($input->server, 'REMOTE_ADDR'),
            userAgent   : $this->readServerValue($input->server, 'HTTP_USER_AGENT')
                              ?? $this->readHeaderValue($input->headers, 'User-Agent')
        );
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
    private function readHeaderValue(array $headers, string $name) : string|null
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
}
