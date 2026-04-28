<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Http;

use Avax\Components\Identity\Auth\Integrations\Cookies\ResolveSessionAllowance;
use Avax\Components\Identity\Auth\Integrations\Headers\ReadBearerToken;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationRequest;
use SensitiveParameter;

/**
 * Maps HTTP transport input into the kernel auth ingress request.
 */
final readonly class MapAuthenticationRequest
{
    private ReadBearerToken $readBearerToken;

    public function __construct(
        #[SensitiveParameter] ReadBearerToken|null            $readBearerToken = null,
        #[SensitiveParameter] private ResolveSessionAllowance $resolveSessionAllowance = new ResolveSessionAllowance()
    )
    {
        $readBearerToken       ??= new ReadBearerToken();
        $this->readBearerToken = $readBearerToken;
    }

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
                              ?? $this->readHeaderValue(headers: $input->headers)
        );
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
    private function readHeaderValue(#[SensitiveParameter] array $headers) : string|null
    {
        foreach ($headers as $candidateKey => $value) {
            if (strcasecmp(string1: $candidateKey, string2: 'User-Agent') !== 0) {
                continue;
            }

            if (is_array(value: $value)) {
                $value = reset(array: $value);
            }

            return is_scalar(value: $value) ? (string) $value : null;
        }

        return null;
    }
}
