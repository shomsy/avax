<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use Avax\Auth\Integrations\Cookies\ResolveSessionAllowance;
use Avax\Auth\Integrations\Headers\ReadBearerToken;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationRequest;
use SensitiveParameter;

/**
 * Maps HTTP transport input into the kernel auth ingress request.
 */
final readonly class MapAuthenticationRequest
{
    public function __construct(
        #[SensitiveParameter] private ReadBearerToken         $readBearerToken = new ReadBearerToken(),
        #[SensitiveParameter] private ResolveSessionAllowance $resolveSessionAllowance = new ResolveSessionAllowance()
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
            ipAddress   : $this->readServerValue(server: $input->server, name: 'REMOTE_ADDR'),
            userAgent   : $this->readServerValue(server: $input->server, name: 'HTTP_USER_AGENT')
                              ?? $this->readHeaderValue(headers: $input->headers, name: 'User-Agent')
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
    private function readHeaderValue(#[SensitiveParameter] array $headers, string $name) : string|null
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
