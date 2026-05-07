<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\CompleteFederatedLogin;

use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\IdGeneratorInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederatedIdentityLink;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederatedIdentityLinkStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationRuntimeInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\FederationFailed;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use Random\RandomException;
use SensitiveParameter;

final readonly class CompleteFederatedLogin
{
    public function __construct(
        private FederationConnectionStoreInterface  $federationConnectionStore,
        private FederationRuntimeInterface          $federationRuntime,
        private FederatedIdentityLinkStoreInterface $federatedIdentityLinkStore,
        private UserSourceInterface                 $userSource,
        private IdentityInterface                   $identity,
        private ProjectAuthenticatedUser            $projectAuthenticatedUser,
        #[SensitiveParameter]
        private CurrentAuthentication               $currentAuthentication,
        #[SensitiveParameter]
        private PasswordHasher                      $passwordHasher,
        private IdGeneratorInterface                $idGenerator,
        private AuditLogInterface                   $auditLog,
        private Clock                               $clock,
        private ?DeterministicRiskEngine            $deterministicRiskEngine = null,
        private ?LifecycleOrchestrator              $lifecycleOrchestrator = null,
    ) {}

    /**
     * @throws FederationFailed
     * @throws RandomException
     */
    public function execute(CompleteFederatedLoginData $completeFederatedLoginData) : AuthenticationResult
    {
        $connection = $this->federationConnectionStore->find(connectionId: $completeFederatedLoginData->connectionId);

        if (! $connection instanceof FederationConnection) {
            throw FederationFailed::notFound();
        }

        if (! $connection->isDomainVerified()) {
            throw FederationFailed::domainNotVerified();
        }

        $federatedIdentity = $this->federationRuntime->completeLogin(payload: $completeFederatedLoginData->payload, connection: $connection);
        $link              = $this->federatedIdentityLinkStore->find(connectionId: $connection->connectionId, subject: $federatedIdentity->subject);
        $user              = $link instanceof FederatedIdentityLink
            ? $this->userSource->findById(id: new UserId(value: $link->userId))
            : null;

        if (! $user instanceof User) {
            $user = $this->userSource->findByEmail(email: $federatedIdentity->email);
        }

        if (! $user instanceof User) {
            $user = $this->provisionUser(email: $federatedIdentity->email, displayName: $federatedIdentity->displayName);
        }

        if (! $user->isActive() || ($this->lifecycleOrchestrator instanceof LifecycleOrchestrator && ! $this->lifecycleOrchestrator->allowsAuthentication(userId: $user->getId()))) {
            throw FederationFailed::notFound();
        }

        if ($this->userSource instanceof ProvisionableUserSourceInterface) {
            $this->userSource->updateEmail(email: $federatedIdentity->email, id: $user->getId());
            $mappedRoles = $this->mapRoles(groupRoleMap: $connection->groupRoleMap, groups: $federatedIdentity->groups);

            if ($mappedRoles !== []) {
                $this->userSource->replaceRoles(roles: $mappedRoles, id: $user->getId());
                $user = $this->userSource->findById(id: $user->getId()) ?? $user;
            }
        }

        $this->federatedIdentityLinkStore->save(link: new FederatedIdentityLink(
                                                          connectionId: $connection->connectionId,
                                                          subject     : $federatedIdentity->subject,
                                                          userId      : $user->getId()->value,
                                                      ));

        $decision             = $this->deterministicRiskEngine?->assessSuccessfulAuthentication(user: $user, ipAddress: $completeFederatedLoginData->ipAddress, userAgent: $completeFederatedLoginData->userAgent);
        $issuedAuthentication = $this->identity->issue(user: $user);
        $this->identity->sessionIdentity()?->captureCurrentSession(
            ipAddress: $completeFederatedLoginData->ipAddress,
            userAgent: $completeFederatedLoginData->userAgent,
        );

        $authenticationContext = AuthenticationContext::authenticated(
            sessionId           : $issuedAuthentication->sessionId,
            accessTokenId       : $issuedAuthentication->accessToken?->tokenId,
            accessTokenExpiresAt: $issuedAuthentication->accessToken?->expiresAt,
            refreshTokenId      : $issuedAuthentication->refreshToken?->tokenId,
            mfaVerifiedAt       : $issuedAuthentication->mfaVerifiedAt,
            user                : $this->projectAuthenticatedUser->fromUser(user: $user),
            mode                : $issuedAuthentication->mode,
        );
        $this->currentAuthentication->store(context: $authenticationContext);

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.federation.login.completed',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'connection_id' => $connection->connectionId,
                                                           'tenant'        => $connection->tenantSlug,
                                                           'user_id'       => $user->getId()->value,
                                                           'risk_action'   => $decision?->action->value,
                                                           'ip_address'    => $completeFederatedLoginData->ipAddress,
                                                           'user_agent'    => $completeFederatedLoginData->userAgent,
                                                       ],
                                       ));

        return AuthenticationResult::success(
            accessToken : $issuedAuthentication->accessToken?->token,
            refreshToken: $issuedAuthentication->refreshToken?->token,
            context     : $authenticationContext,
        );
    }

    /**
     * @throws RandomException
     */
    private function provisionUser(#[SensitiveParameter] string $email, string $displayName) : User
    {
        $username = $this->uniqueUsername(displayName: $displayName, email: $email);
        $password = bin2hex(string: random_bytes(length: 24));

        return $this->userSource->create(user: User::create(
            username    : $username,
            passwordHash: $this->passwordHasher->hash(password: $password),
            id          : new UserId(value: $this->idGenerator->generate()),
            email       : new UserEmail(value: $email),
        ));
    }

    private function uniqueUsername(string $displayName, #[SensitiveParameter] string $email) : string
    {
        $normalizedDisplayName = preg_replace(pattern: '/[^a-z0-9]+/i', replacement: '-', subject: strtolower(string: trim(string: $displayName)));
        $base                  = $normalizedDisplayName !== null && $normalizedDisplayName !== ''
            ? $normalizedDisplayName
            : explode(separator: '@', string: $email)[0];
        $candidate             = trim(string: $base, characters: '-');

        if ($candidate === '') {
            $candidate = 'federated-user';
        }

        $username = $candidate;
        $suffix   = 1;

        while ( $this->userSource->usernameExists(username: $username) ) {
            $suffix++;
            $username = $candidate . '-' . $suffix;
        }

        return $username;
    }

    /**
     * @param array<string, list<string>> $groupRoleMap
     * @param list<string>                $groups
     *
     * @return list<UserRole>
     */
    private function mapRoles(array $groupRoleMap, array $groups) : array
    {
        $roles = [];

        foreach ($groups as $group) {
            foreach ($groupRoleMap[$group] ?? [] as $roleValue) {
                $role = UserRole::tryFrom(value: $roleValue);

                if ($role !== null && ! in_array(needle: $role, haystack: $roles, strict: true)) {
                    $roles[] = $role;
                }
            }
        }

        return $roles;
    }
}
