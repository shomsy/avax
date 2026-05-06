<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraintType;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
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
        #[SensitiveParameter]
        private readonly PasswordHasher $passwordHasher,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function register(
        string $name,
        OAuthClientType $oAuthClientType,
        array $redirectUris,
        ?string $tenantSlug = null,
        array $allowedScopes = [],
        array $allowedAudiences = [],
        array $allowedGrantTypes = [],
        array $audienceScopeBoundaries = [],
        #[SensitiveParameter]
        ?OAuthTokenEndpointAuthMethod $oAuthTokenEndpointAuthMethod = null,
        ?OAuthSenderConstraintType $oAuthSenderConstraintType = null,
        bool $workloadIdentity = false,
        bool $phishingResistantRequired = false,
        bool $requestObjectSignatureRequired = false,
        bool $frontChannelLogoutSupported = false,
        bool $backChannelLogoutSupported = false,
        ?bool $approvalRequired = null,
        #[SensitiveParameter]
        ?string $requestObjectVerificationKeyPem = null,
    ): RegisteredOAuthClient {
        $normalizedRedirectUris = $this->normalizeRedirectUris(redirectUris: $redirectUris);
        $normalizedScopes = $this->normalizeScopes(allowedScopes: $allowedScopes);
        $normalizedAudiences = $this->normalizeStrings(values: $allowedAudiences);
        $normalizedGrantTypes = $this->normalizeGrantTypes(allowedGrantTypes: $allowedGrantTypes, type: $oAuthClientType);
        $normalizedAudienceScopeBoundaries = $this->normalizeAudienceScopeBoundaries(audienceScopeBoundaries: $audienceScopeBoundaries);

        if ($normalizedRedirectUris === []) {
            throw new InvalidArgumentException(message: 'OAuth clients require at least one redirect URI.');
        }

        if ($workloadIdentity && $oAuthClientType !== OAuthClientType::CONFIDENTIAL) {
            throw new InvalidArgumentException(message: 'Workload identity clients must be confidential.');
        }

        if ($workloadIdentity && ! $oAuthSenderConstraintType instanceof OAuthSenderConstraintType) {
            throw new InvalidArgumentException(message: 'Workload identity clients require sender-constrained tokens.');
        }

        if ($workloadIdentity && $normalizedAudiences === []) {
            throw new InvalidArgumentException(message: 'Workload identity clients require at least one allowed audience.');
        }

        $normalizedTokenEndpointAuthMethod = new OAuthTokenEndpointAuthMethodPolicy()->resolve(
            requested       : $oAuthTokenEndpointAuthMethod,
            workloadIdentity: $workloadIdentity,
            type            : $oAuthClientType,
        );
        $requiresApproval = $approvalRequired ?? false;

        foreach (array_keys(array: $normalizedAudienceScopeBoundaries) as $audience) {
            if (! in_array(needle: $audience, haystack: $normalizedAudiences, strict: true)) {
                throw new InvalidArgumentException(message: 'Audience scope boundaries must target a declared allowed audience.');
            }
        }

        $clientId = 'oauth_'.bin2hex(string: random_bytes(length: 12));
        $plainSecret = null;
        $secretHash = null;

        if ($oAuthClientType === OAuthClientType::CONFIDENTIAL) {
            $plainSecret = bin2hex(string: random_bytes(length: 24));
            $secretHash = $this->passwordHasher->hash(password: $plainSecret);
        }

        $oAuthClient = new OAuthClient(
            clientId                       : $clientId,
            name                           : trim(string: $name),
            type                           : $oAuthClientType,
            redirectUris                   : $normalizedRedirectUris,
            allowedScopes                  : $normalizedScopes,
            tenantSlug                     : $this->normalizeTenantSlug(tenantSlug: $tenantSlug),
            allowedAudiences               : $normalizedAudiences,
            allowedGrantTypes              : $normalizedGrantTypes,
            audienceScopeBoundaries        : $normalizedAudienceScopeBoundaries,
            requiredSenderConstraint       : $oAuthSenderConstraintType,
            workloadIdentity               : $workloadIdentity,
            phishingResistantRequired      : $phishingResistantRequired,
            requestObjectSignatureRequired : $requestObjectSignatureRequired,
            frontChannelLogoutSupported    : $frontChannelLogoutSupported,
            backChannelLogoutSupported     : $backChannelLogoutSupported,
            approvedAt                     : $requiresApproval ? null : new DateTimeImmutable(),
            approvedBy                     : $requiresApproval ? null : 'system',
            active                         : ! $requiresApproval,
            secretHash                     : $secretHash,
            requestObjectVerificationKeyPem: $requestObjectVerificationKeyPem,
            tokenEndpointAuthMethod        : $normalizedTokenEndpointAuthMethod,
            approvalStatus                 : $requiresApproval
                                                 ? OAuthClientApprovalStatus::PENDING_APPROVAL
                                                 : OAuthClientApprovalStatus::APPROVED,
        );

        $this->clients[$clientId] = $oAuthClient;

        return new RegisteredOAuthClient(
            client         : $oAuthClient,
            plainTextSecret: $plainSecret,
        );
    }

    /**
     * @param  list<string>  $redirectUris
     * @return list<string>
     */
    private function normalizeRedirectUris(array $redirectUris): array
    {
        $normalized = [];

        foreach ($redirectUris as $redirectUri) {
            $value = trim(string: $redirectUri);
            if ($value === '') {
                continue;
            }

            if (in_array(needle: $value, haystack: $normalized, strict: true)) {
                continue;
            }

            $normalized[] = $value;
        }

        return $normalized;
    }

    /**
     * @param  list<string>  $allowedScopes
     * @return list<string>
     */
    private function normalizeScopes(array $allowedScopes): array
    {
        return $this->normalizeStrings(values: $allowedScopes);
    }

    /**
     * @param  list<string>  $values
     * @return list<string>
     */
    private function normalizeStrings(array $values): array
    {
        $normalized = [];

        foreach ($values as $value) {
            $value = trim(string: $value);
            if ($value === '') {
                continue;
            }

            if (in_array(needle: $value, haystack: $normalized, strict: true)) {
                continue;
            }

            $normalized[] = $value;
        }

        sort(array: $normalized);

        return $normalized;
    }

    /**
     * @param  list<OAuthGrantType>  $allowedGrantTypes
     * @return list<OAuthGrantType>
     */
    private function normalizeGrantTypes(OAuthClientType $oAuthClientType, array $allowedGrantTypes): array
    {
        if ($allowedGrantTypes === []) {
            return [
                OAuthGrantType::AUTHORIZATION_CODE,
                OAuthGrantType::REFRESH_TOKEN,
            ];
        }

        $normalized = [];

        foreach ($allowedGrantTypes as $allowedGrantType) {
            if (in_array(needle: $allowedGrantType, haystack: $normalized, strict: true)) {
                continue;
            }

            if ($allowedGrantType === OAuthGrantType::CLIENT_CREDENTIALS && $oAuthClientType !== OAuthClientType::CONFIDENTIAL) {
                throw new InvalidArgumentException(message: 'Public clients cannot use the client credentials grant.');
            }

            $normalized[] = $allowedGrantType;
        }

        return $normalized;
    }

    /**
     * @param  array<string, list<string>>  $audienceScopeBoundaries
     * @return array<string, list<string>>
     */
    private function normalizeAudienceScopeBoundaries(array $audienceScopeBoundaries): array
    {
        $normalized = [];

        foreach ($audienceScopeBoundaries as $audience => $scopes) {
            $normalizedAudience = trim(string: $audience);

            if ($normalizedAudience === '') {
                continue;
            }

            $normalized[$normalizedAudience] = $this->normalizeStrings(values: $scopes);
        }

        ksort(array: $normalized);

        return $normalized;
    }

    private function normalizeTenantSlug(?string $tenantSlug): ?string
    {
        $normalized = trim(string: (string) $tenantSlug);

        return $normalized !== '' ? strtolower(string: $normalized) : null;
    }

    public function replace(OAuthClient $oAuthClient): void
    {
        $this->clients[$oAuthClient->clientId] = $oAuthClient;
    }

    public function deactivate(string $clientId): ?OAuthClient
    {
        $client = $this->find(clientId: $clientId);

        if (! $client instanceof OAuthClient) {
            return null;
        }

        $oAuthClient = new OAuthClient(
            clientId                      : $client->clientId,
            name                          : $client->name,
            type                          : $client->type,
            redirectUris                  : $client->redirectUris,
            allowedScopes                 : $client->allowedScopes,
            tenantSlug                    : $client->tenantSlug,
            allowedAudiences              : $client->allowedAudiences,
            allowedGrantTypes             : $client->allowedGrantTypes,
            audienceScopeBoundaries       : $client->audienceScopeBoundaries,
            requiredSenderConstraint      : $client->requiredSenderConstraint,
            workloadIdentity              : $client->workloadIdentity,
            phishingResistantRequired     : $client->phishingResistantRequired,
            requestObjectSignatureRequired: $client->requestObjectSignatureRequired,
            frontChannelLogoutSupported   : $client->frontChannelLogoutSupported,
            backChannelLogoutSupported    : $client->backChannelLogoutSupported,
            approvedAt                    : $client->approvedAt,
            approvedBy                    : $client->approvedBy,
            active                        : false,
            secretHash                    : $client->secretHash,
            tokenEndpointAuthMethod       : $client->tokenEndpointAuthMethod,
            approvalStatus                : $client->approvalStatus,
        );
        $this->clients[$clientId] = $oAuthClient;

        return $oAuthClient;
    }

    public function find(string $clientId): ?OAuthClient
    {
        return $this->clients[$clientId] ?? null;
    }

    public function approve(string $clientId, string $approvedBy): ?OAuthClient
    {
        $client = $this->find(clientId: $clientId);

        if (! $client instanceof OAuthClient) {
            return null;
        }

        $oAuthClient = new OAuthClient(
            clientId                      : $client->clientId,
            name                          : $client->name,
            type                          : $client->type,
            redirectUris                  : $client->redirectUris,
            allowedScopes                 : $client->allowedScopes,
            tenantSlug                    : $client->tenantSlug,
            allowedAudiences              : $client->allowedAudiences,
            allowedGrantTypes             : $client->allowedGrantTypes,
            audienceScopeBoundaries       : $client->audienceScopeBoundaries,
            requiredSenderConstraint      : $client->requiredSenderConstraint,
            workloadIdentity              : $client->workloadIdentity,
            phishingResistantRequired     : $client->phishingResistantRequired,
            requestObjectSignatureRequired: $client->requestObjectSignatureRequired,
            frontChannelLogoutSupported   : $client->frontChannelLogoutSupported,
            backChannelLogoutSupported    : $client->backChannelLogoutSupported,
            approvedAt                    : new DateTimeImmutable(),
            approvedBy                    : trim(string: $approvedBy),
            active                        : true,
            secretHash                    : $client->secretHash,
            tokenEndpointAuthMethod       : $client->tokenEndpointAuthMethod,
            approvalStatus                : OAuthClientApprovalStatus::APPROVED,
        );
        $this->clients[$clientId] = $oAuthClient;

        return $oAuthClient;
    }

    /**
     * @throws RandomException
     */
    public function rotateSecret(string $clientId): ?RegisteredOAuthClient
    {
        $client = $this->find(clientId: $clientId);

        if (! $client instanceof OAuthClient) {
            return null;
        }

        if ($client->isPublic()) {
            return new RegisteredOAuthClient(client: $client);
        }

        $plainSecret = bin2hex(string: random_bytes(length: 24));
        $oAuthClient = new OAuthClient(
            clientId                      : $client->clientId,
            name                          : $client->name,
            type                          : $client->type,
            redirectUris                  : $client->redirectUris,
            allowedScopes                 : $client->allowedScopes,
            tenantSlug                    : $client->tenantSlug,
            allowedAudiences              : $client->allowedAudiences,
            allowedGrantTypes             : $client->allowedGrantTypes,
            audienceScopeBoundaries       : $client->audienceScopeBoundaries,
            requiredSenderConstraint      : $client->requiredSenderConstraint,
            workloadIdentity              : $client->workloadIdentity,
            phishingResistantRequired     : $client->phishingResistantRequired,
            requestObjectSignatureRequired: $client->requestObjectSignatureRequired,
            frontChannelLogoutSupported   : $client->frontChannelLogoutSupported,
            backChannelLogoutSupported    : $client->backChannelLogoutSupported,
            approvedAt                    : $client->approvedAt,
            approvedBy                    : $client->approvedBy,
            active                        : $client->active,
            secretHash                    : $this->passwordHasher->hash(password: $plainSecret),
            tokenEndpointAuthMethod       : $client->tokenEndpointAuthMethod,
            approvalStatus                : $client->approvalStatus,
        );
        $this->clients[$clientId] = $oAuthClient;

        return new RegisteredOAuthClient(client: $oAuthClient, plainTextSecret: $plainSecret);
    }

    public function all(): array
    {
        return array_values(array: $this->clients);
    }

    public function verifySecret(
        string $clientId,
        #[SensitiveParameter]
        ?string $plainTextSecret,
    ): bool {
        $client = $this->find(clientId: $clientId);

        if (! $client instanceof OAuthClient || ! $client->isActive()) {
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
}
