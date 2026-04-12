<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\AuthorizeCode;

use Avax\Auth\System\Capability\OAuth\AuthorizationCodeStoreInterface;
use Avax\Auth\System\Capability\OAuth\IssuedAuthorizationCode;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Capability\OAuth\PkceMethod;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\OAuth\OAuthAuthorizationFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class AuthorizeCode
{
    public function __construct(
        private CurrentAuthentication        $currentAuthentication,
        private UserSourceInterface          $userSource,
        private OAuthClientRegistryInterface $clientRegistry,
        private AuthorizationCodeStoreInterface $codeStore,
        private AuditLogInterface            $auditLog,
        private Clock                        $clock
    ) {}

    /**
     * @throws OAuthAuthorizationFailed
     */
    public function execute(AuthorizeCodeData $data) : IssuedAuthorizationCode
    {
        $context = $this->currentAuthentication->read();
        $actor   = $context->user();
        $now     = $this->clock->now();

        if ($actor === null) {
            $this->recordFailure($data, 'unauthenticated');
            throw OAuthAuthorizationFailed::unauthenticated();
        }

        $user = $this->userSource->findById(new UserId($actor->id));

        if ($user === null || ! $user->isActive()) {
            $this->recordFailure($data, 'user_not_active');
            throw OAuthAuthorizationFailed::unauthenticated();
        }

        $client = $this->clientRegistry->find($data->clientId);

        if ($client === null) {
            $this->recordFailure($data, 'client_not_found');
            throw OAuthAuthorizationFailed::invalidClient();
        }

        if (! $client->allowsRedirectUri($data->redirectUri)) {
            $this->recordFailure($data, 'redirect_uri_mismatch');
            throw OAuthAuthorizationFailed::invalidRedirectUri();
        }

        $scopes = $this->normalizeScopes($data->scopes);

        if (! $client->allowsScopes($scopes)) {
            $this->recordFailure($data, 'scope_mismatch');
            throw OAuthAuthorizationFailed::invalidScopes();
        }

        if ($client->isPublic()) {
            if ($data->codeChallenge === null || $data->codeChallengeMethod !== PkceMethod::S256) {
                $this->recordFailure($data, 'pkce_required');
                throw OAuthAuthorizationFailed::invalidPkce();
            }
        }

        $issued = $this->codeStore->issue(
            userId             : $user->getId(),
            clientId           : $client->clientId,
            redirectUri        : $data->redirectUri,
            scopes             : $scopes,
            expiresAt          : $now->modify('+5 minutes'),
            state              : $data->state,
            codeChallenge      : $data->codeChallenge,
            codeChallengeMethod: $data->codeChallengeMethod,
            mfaVerifiedAt      : $context->mfaVerifiedAt(),
            phishingResistant  : $context->isPhishingResistant()
        );

        $this->auditLog->record(new AuditEvent(
            name      : 'auth.oauth.authorization_code.issued',
            occurredAt: $now,
            context   : [
                'client_id' => $client->clientId,
                'user_id' => $user->getId()->value,
                'code_id' => $issued->codeId,
                'scope' => implode(' ', $scopes),
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]
        ));

        return $issued;
    }

    /**
     * @param list<string> $scopes
     * @return list<string>
     */
    private function normalizeScopes(array $scopes) : array
    {
        $normalized = [];

        foreach ($scopes as $scope) {
            $value = trim($scope);

            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            $normalized[] = $value;
        }

        sort($normalized);

        return $normalized;
    }

    private function recordFailure(AuthorizeCodeData $data, string $reason) : void
    {
        $this->auditLog->record(new AuditEvent(
            name      : 'auth.oauth.authorization_code.failed',
            occurredAt: $this->clock->now(),
            context   : [
                'client_id' => $data->clientId,
                'redirect_uri' => $data->redirectUri,
                'reason' => $reason,
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]
        ));
    }
}
