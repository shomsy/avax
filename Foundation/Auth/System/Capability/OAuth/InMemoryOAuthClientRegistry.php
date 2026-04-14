<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use DateTimeImmutable;
use InvalidArgumentException;
use Random\RandomException;
use SensitiveParameter;

/**
 * In-memory OAuth client registry for tests and demos.
 */
final class InMemoryOAuthClientRegistry implements OAuthClientRegistryInterface
{
    /** @var array<string, OAuthClient> */
    private array $clients = [];

    public function __construct(
        #[SensitiveParameter] private readonly PasswordHasher $passwordHasher
    ) {}

    /**
     * @throws RandomException
     */
    public function register(
        string $name,
        OAuthClientType $type,
        array $redirectUris,
        string|null $tenantSlug = null,
        array $allowedScopes = [],
        array $allowedAudiences = [],
        array $allowedGrantTypes = [],
        array $audienceScopeBoundaries = [],
        OAuthTokenEndpointAuthMethod|null $tokenEndpointAuthMethod = null,
        OAuthSenderConstraintType|null $requiredSenderConstraint = null,
        bool $workloadIdentity = false,
        bool $phishingResistantRequired = false,
        bool $frontChannelLogoutSupported = false,
        bool $backChannelLogoutSupported = false,
        bool|null $approvalRequired = null
    ) : RegisteredOAuthClient
    {
        $normalizedRedirectUris = $this->normalizeRedirectUris(redirectUris: $redirectUris);
        $normalizedScopes       = $this->normalizeScopes(allowedScopes: $allowedScopes);
        $normalizedAudiences    = $this->normalizeStrings(values: $allowedAudiences);
        $normalizedGrantTypes   = $this->normalizeGrantTypes(type: $type, allowedGrantTypes: $allowedGrantTypes);
        $normalizedAudienceScopeBoundaries = $this->normalizeAudienceScopeBoundaries(audienceScopeBoundaries: $audienceScopeBoundaries);

        if ($normalizedRedirectUris === []) {
            throw new InvalidArgumentException(message: 'OAuth clients require at least one redirect URI.');
        }

        if ($workloadIdentity && $type !== OAuthClientType::CONFIDENTIAL) {
            throw new InvalidArgumentException(message: 'Workload identity clients must be confidential.');
        }

        if ($workloadIdentity && $requiredSenderConstraint === null) {
            throw new InvalidArgumentException(message: 'Workload identity clients require sender-constrained tokens.');
        }

        if ($workloadIdentity && $normalizedAudiences === []) {
            throw new InvalidArgumentException(message: 'Workload identity clients require at least one allowed audience.');
        }

        $normalizedTokenEndpointAuthMethod = (new OAuthTokenEndpointAuthMethodPolicy())->resolve(
            type: $type,
            requested: $tokenEndpointAuthMethod,
            workloadIdentity: $workloadIdentity
        );
        $requiresApproval = $approvalRequired ?? false;

        foreach (array_keys($normalizedAudienceScopeBoundaries) as $audience) {
            if (! in_array($audience, $normalizedAudiences, true)) {
                throw new InvalidArgumentException(message: 'Audience scope boundaries must target a declared allowed audience.');
            }
        }

        $clientId   = 'oauth_' . bin2hex(random_bytes(12));
        $plainSecret = null;
        $secretHash = null;

        if ($type === OAuthClientType::CONFIDENTIAL) {
            $plainSecret = bin2hex(random_bytes(24));
            $secretHash  = $this->passwordHasher->hash(password: $plainSecret);
        }

        $client = new OAuthClient(
            clientId     : $clientId,
            name         : trim($name),
            type         : $type,
            redirectUris : $normalizedRedirectUris,
            allowedScopes: $normalizedScopes,
            tenantSlug   : $this->normalizeTenantSlug(tenantSlug: $tenantSlug),
            allowedAudiences: $normalizedAudiences,
            allowedGrantTypes: $normalizedGrantTypes,
            audienceScopeBoundaries: $normalizedAudienceScopeBoundaries,
            tokenEndpointAuthMethod: $normalizedTokenEndpointAuthMethod,
            requiredSenderConstraint: $requiredSenderConstraint,
            workloadIdentity: $workloadIdentity,
            phishingResistantRequired: $phishingResistantRequired,
            frontChannelLogoutSupported: $frontChannelLogoutSupported,
            backChannelLogoutSupported: $backChannelLogoutSupported,
            approvalStatus: $requiresApproval
                ? OAuthClientApprovalStatus::PENDING_APPROVAL
                : OAuthClientApprovalStatus::APPROVED,
            approvedAt   : $requiresApproval ? null : new DateTimeImmutable(),
            approvedBy   : $requiresApproval ? null : 'system',
            active       : ! $requiresApproval,
            secretHash   : $secretHash
        );

        $this->clients[$clientId] = $client;

        return new RegisteredOAuthClient(
            client         : $client,
            plainTextSecret: $plainSecret
        );
    }

    public function replace(OAuthClient $client) : void
    {
        $this->clients[$client->clientId] = $client;
    }

    public function deactivate(string $clientId) : OAuthClient|null
    {
        $client = $this->find(clientId: $clientId);

        if ($client === null) {
            return null;
        }

        $inactive = new OAuthClient(
            clientId                  : $client->clientId,
            name                      : $client->name,
            type                      : $client->type,
            redirectUris              : $client->redirectUris,
            allowedScopes             : $client->allowedScopes,
            tenantSlug                : $client->tenantSlug,
            allowedAudiences          : $client->allowedAudiences,
            allowedGrantTypes         : $client->allowedGrantTypes,
            audienceScopeBoundaries   : $client->audienceScopeBoundaries,
            tokenEndpointAuthMethod   : $client->tokenEndpointAuthMethod,
            requiredSenderConstraint  : $client->requiredSenderConstraint,
            workloadIdentity          : $client->workloadIdentity,
            phishingResistantRequired : $client->phishingResistantRequired,
            frontChannelLogoutSupported: $client->frontChannelLogoutSupported,
            backChannelLogoutSupported : $client->backChannelLogoutSupported,
            approvalStatus           : $client->approvalStatus,
            approvedAt               : $client->approvedAt,
            approvedBy               : $client->approvedBy,
            active                    : false,
            secretHash                : $client->secretHash
        );
        $this->clients[$clientId] = $inactive;

        return $inactive;
    }

    public function approve(string $clientId, string $approvedBy) : OAuthClient|null
    {
        $client = $this->find(clientId: $clientId);

        if ($client === null) {
            return null;
        }

        $approved = new OAuthClient(
            clientId                  : $client->clientId,
            name                      : $client->name,
            type                      : $client->type,
            redirectUris              : $client->redirectUris,
            allowedScopes             : $client->allowedScopes,
            tenantSlug                : $client->tenantSlug,
            allowedAudiences          : $client->allowedAudiences,
            allowedGrantTypes         : $client->allowedGrantTypes,
            audienceScopeBoundaries   : $client->audienceScopeBoundaries,
            tokenEndpointAuthMethod   : $client->tokenEndpointAuthMethod,
            requiredSenderConstraint  : $client->requiredSenderConstraint,
            workloadIdentity          : $client->workloadIdentity,
            phishingResistantRequired : $client->phishingResistantRequired,
            frontChannelLogoutSupported: $client->frontChannelLogoutSupported,
            backChannelLogoutSupported : $client->backChannelLogoutSupported,
            approvalStatus            : OAuthClientApprovalStatus::APPROVED,
            approvedAt                : new DateTimeImmutable(),
            approvedBy                : trim($approvedBy),
            active                    : true,
            secretHash                : $client->secretHash
        );
        $this->clients[$clientId] = $approved;

        return $approved;
    }

    /**
     * @throws RandomException
     */
    public function rotateSecret(string $clientId) : RegisteredOAuthClient|null
    {
        $client = $this->find(clientId: $clientId);

        if ($client === null) {
            return null;
        }

        if ($client->isPublic()) {
            return new RegisteredOAuthClient(client: $client, plainTextSecret: null);
        }

        $plainSecret = bin2hex(random_bytes(24));
        $rotated = new OAuthClient(
            clientId                  : $client->clientId,
            name                      : $client->name,
            type                      : $client->type,
            redirectUris              : $client->redirectUris,
            allowedScopes             : $client->allowedScopes,
            tenantSlug                : $client->tenantSlug,
            allowedAudiences          : $client->allowedAudiences,
            allowedGrantTypes         : $client->allowedGrantTypes,
            audienceScopeBoundaries   : $client->audienceScopeBoundaries,
            tokenEndpointAuthMethod   : $client->tokenEndpointAuthMethod,
            requiredSenderConstraint  : $client->requiredSenderConstraint,
            workloadIdentity          : $client->workloadIdentity,
            phishingResistantRequired : $client->phishingResistantRequired,
            frontChannelLogoutSupported: $client->frontChannelLogoutSupported,
            backChannelLogoutSupported : $client->backChannelLogoutSupported,
            approvalStatus            : $client->approvalStatus,
            approvedAt                : $client->approvedAt,
            approvedBy                : $client->approvedBy,
            active                    : $client->active,
            secretHash                : $this->passwordHasher->hash(password: $plainSecret)
        );
        $this->clients[$clientId] = $rotated;

        return new RegisteredOAuthClient(client: $rotated, plainTextSecret: $plainSecret);
    }

    public function find(string $clientId) : OAuthClient|null
    {
        return $this->clients[$clientId] ?? null;
    }

    public function all() : array
    {
        return array_values($this->clients);
    }

    public function verifySecret(
        string $clientId,
        #[SensitiveParameter] string|null $plainTextSecret
    ) : bool
    {
        $client = $this->find(clientId: $clientId);

        if ($client === null) {
            return false;
        }

        if (! $client->isActive()) {
            return false;
        }

        if ($client->isPublic()) {
            return $plainTextSecret === null || $plainTextSecret === '';
        }

        if ($plainTextSecret === null || $plainTextSecret === '' || $client->secretHash === null) {
            return false;
        }

        return $this->passwordHasher->verify(password: $plainTextSecret, hash: $client->secretHash);
    }

    /**
     * @param list<string> $redirectUris
     * @return list<string>
     */
    private function normalizeRedirectUris(array $redirectUris) : array
    {
        $normalized = [];

        foreach ($redirectUris as $redirectUri) {
            $value = trim($redirectUri);

            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            $normalized[] = $value;
        }

        return $normalized;
    }

    /**
     * @param list<string> $allowedScopes
     * @return list<string>
     */
    private function normalizeScopes(array $allowedScopes) : array
    {
        return $this->normalizeStrings(values: $allowedScopes);
    }

    /**
     * @param list<string> $values
     * @return list<string>
     */
    private function normalizeStrings(array $values) : array
    {
        $normalized = [];

        foreach ($values as $value) {
            $value = trim($value);

            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            $normalized[] = $value;
        }

        sort($normalized);

        return $normalized;
    }

    /**
     * @param array<string, list<string>> $audienceScopeBoundaries
     * @return array<string, list<string>>
     */
    private function normalizeAudienceScopeBoundaries(array $audienceScopeBoundaries) : array
    {
        $normalized = [];

        foreach ($audienceScopeBoundaries as $audience => $scopes) {
            $normalizedAudience = trim($audience);

            if ($normalizedAudience === '') {
                continue;
            }

            $normalized[$normalizedAudience] = $this->normalizeStrings(values: $scopes);
        }

        ksort($normalized);

        return $normalized;
    }

    private function normalizeTenantSlug(string|null $tenantSlug) : string|null
    {
        $normalized = trim((string) $tenantSlug);

        return $normalized !== '' ? strtolower($normalized) : null;
    }

    /**
     * @param list<OAuthGrantType> $allowedGrantTypes
     * @return list<OAuthGrantType>
     */
    private function normalizeGrantTypes(OAuthClientType $type, array $allowedGrantTypes) : array
    {
        if ($allowedGrantTypes === []) {
            return [
                OAuthGrantType::AUTHORIZATION_CODE,
                OAuthGrantType::REFRESH_TOKEN,
            ];
        }

        $normalized = [];

        foreach ($allowedGrantTypes as $grantType) {
            if (in_array($grantType, $normalized, true)) {
                continue;
            }

            if ($grantType === OAuthGrantType::CLIENT_CREDENTIALS && $type !== OAuthClientType::CONFIDENTIAL) {
                throw new InvalidArgumentException(message: 'Public clients cannot use the client credentials grant.');
            }

            $normalized[] = $grantType;
        }

        return $normalized;
    }
}
