<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest;

use Avax\Auth\System\Capability\Oidc\OidcRequestObjectStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use DateTimeImmutable;
use SensitiveParameter;
use InvalidArgumentException;

final readonly class PushAuthorizationRequest
{
    public function __construct(
        private OidcRequestObjectStoreInterface $requestObjectStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    public function execute(PushAuthorizationRequestData $data) : PushedAuthorizationRequest
    {
        $clientId = trim($data->clientId);
        $redirectUri = trim($data->redirectUri);
        $now = $this->clock->now();

        if ($clientId === '' || $redirectUri === '') {
            throw new InvalidArgumentException(message: 'PAR requests require client and redirect URIs.');
        }

        $requestUri = 'urn:ietf:params:oauth:request_uri:' . bin2hex(random_bytes(16));
        $expiresAt = $now->modify(modifier: '+5 minutes');
        $claims = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $this->normalizeScopes(scopes: $data->scopes)),
            'state' => $data->state !== null ? trim($data->state) : null,
            'nonce' => $data->nonce !== null ? trim($data->nonce) : null,
            'code_challenge' => $data->codeChallenge !== null ? trim($data->codeChallenge) : null,
            'code_challenge_method' => $data->codeChallengeMethod?->value,
        ];

        $this->requestObjectStore->store(
            requestUri: $requestUri,
            claims    : $claims,
            expiresAt  : $expiresAt
        );

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oidc.par.pushed',
            occurredAt: $now,
            context   : [
                'client_id' => $clientId,
                'request_uri' => $requestUri,
                'redirect_uri' => $redirectUri,
            ]
        ));

        return new PushedAuthorizationRequest(
            requestUri        : $requestUri,
            expiresAt         : $expiresAt,
            clientId          : $clientId,
            redirectUri       : $redirectUri,
            scopes            : $this->normalizeScopes(scopes: $data->scopes),
            state             : $data->state !== null ? trim($data->state) : null,
            nonce             : $data->nonce !== null ? trim($data->nonce) : null,
            codeChallenge     : $data->codeChallenge !== null ? trim($data->codeChallenge) : null,
            codeChallengeMethod: $data->codeChallengeMethod
        );
    }

    /**
     * @param list<string> $scopes
     * @return list<string>
     */
    private function normalizeScopes(array $scopes) : array
    {
        $normalized = [];

        foreach ($scopes as $scope) {
            $parts = preg_split(pattern: '/\s+/', subject: trim($scope), flags: PREG_SPLIT_NO_EMPTY);

            if ($parts === false) {
                continue;
            }

            foreach ($parts as $value) {
                if (in_array(needle: $value, haystack: $normalized, strict: true)) {
                    continue;
                }

                $normalized[] = $value;
            }
        }

        sort(array: $normalized);

        return $normalized;
    }
}
