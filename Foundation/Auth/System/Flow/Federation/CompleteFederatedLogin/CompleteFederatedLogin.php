<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\CompleteFederatedLogin;

use Avax\Auth\System\Capability\Federation\FederatedIdentityLink;
use Avax\Auth\System\Capability\Federation\FederatedIdentityLinkStoreInterface;
use Avax\Auth\System\Capability\Federation\FederationConnectionStoreInterface;
use Avax\Auth\System\Capability\Federation\FederationRuntimeInterface;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Lifecycle\LifecycleOrchestrator;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\Risk\DeterministicRiskEngine;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Capability\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Federation\FederationFailed;
use Avax\Auth\System\Flow\Login\AuthenticationResult;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use Random\RandomException;
use SensitiveParameter;

final readonly class CompleteFederatedLogin
{
    private LifecycleOrchestrator|null          $lifecycle;
    private DeterministicRiskEngine|null        $riskEngine;
    private Clock                               $clock;
    private AuditLogInterface                   $auditLog;
    private IdGeneratorInterface                $idGenerator;
    private PasswordHasher                      $passwordHasher;
    private CurrentAuthentication               $currentAuthentication;
    private ProjectAuthenticatedUser            $projectAuthenticatedUser;
    private IdentityInterface                   $identity;
    private UserSourceInterface                 $userSource;
    private FederatedIdentityLinkStoreInterface $linkStore;
    private FederationRuntimeInterface          $runtime;
    private FederationConnectionStoreInterface  $connectionStore;

    public function __construct(
        FederationConnectionStoreInterface          $connectionStore,
        FederationRuntimeInterface                  $runtime,
        FederatedIdentityLinkStoreInterface         $linkStore,
        UserSourceInterface                         $userSource,
        IdentityInterface                           $identity,
        ProjectAuthenticatedUser                    $projectAuthenticatedUser,
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication,
        #[SensitiveParameter] PasswordHasher        $passwordHasher,
        IdGeneratorInterface                        $idGenerator,
        AuditLogInterface                           $auditLog,
        Clock                                       $clock,
        DeterministicRiskEngine|null                $riskEngine = null,
        LifecycleOrchestrator|null                  $lifecycle = null
    )
    {
        $this->connectionStore          = $connectionStore;
        $this->runtime                  = $runtime;
        $this->linkStore                = $linkStore;
        $this->userSource               = $userSource;
        $this->identity                 = $identity;
        $this->projectAuthenticatedUser = $projectAuthenticatedUser;
        $this->currentAuthentication    = $currentAuthentication;
        $this->passwordHasher           = $passwordHasher;
        $this->idGenerator              = $idGenerator;
        $this->auditLog                 = $auditLog;
        $this->clock                    = $clock;
        $this->riskEngine               = $riskEngine;
        $this->lifecycle                = $lifecycle;
    }

    /**
     * @throws FederationFailed
     */
    public function execute(CompleteFederatedLoginData $data) : AuthenticationResult
    {
        $connection = $this->connectionStore->find(connectionId: $data->connectionId);

        if ($connection === null) {
            throw FederationFailed::notFound();
        }

        if (! $connection->isDomainVerified()) {
            throw FederationFailed::domainNotVerified();
        }

        $federated = $this->runtime->completeLogin(connection: $connection, payload: $data->payload);
        $link      = $this->linkStore->find(connectionId: $connection->connectionId, subject: $federated->subject);
        $user      = $link !== null
            ? $this->userSource->findById(id: new UserId(value: $link->userId))
            : null;

        if ($user === null) {
            $user = $this->userSource->findByEmail(email: $federated->email);
        }

        if ($user === null) {
            $user = $this->provisionUser(email: $federated->email, displayName: $federated->displayName);
        }

        if (! $user->isActive() || ($this->lifecycle !== null && ! $this->lifecycle->allowsAuthentication(userId: $user->getId()))) {
            throw FederationFailed::notFound();
        }

        if ($this->userSource instanceof ProvisionableUserSourceInterface) {
            $this->userSource->updateEmail(id: $user->getId(), email: $federated->email);
            $mappedRoles = $this->mapRoles(groupRoleMap: $connection->groupRoleMap, groups: $federated->groups);

            if ($mappedRoles !== []) {
                $this->userSource->replaceRoles(id: $user->getId(), roles: $mappedRoles);
                $user = $this->userSource->findById(id: $user->getId()) ?? $user;
            }
        }

        $this->linkStore->save(link: new FederatedIdentityLink(
                                         connectionId: $connection->connectionId,
                                         subject     : $federated->subject,
                                         userId      : $user->getId()->value
                                     ));

        $decision = $this->riskEngine?->assessSuccessfulAuthentication(user: $user, ipAddress: $data->ipAddress, userAgent: $data->userAgent);
        $issued   = $this->identity->issue(user: $user);
        $this->identity->sessionIdentity()?->captureCurrentSession(
            ipAddress: $data->ipAddress,
            userAgent: $data->userAgent
        );

        $context = AuthenticationContext::authenticated(
            user                : $this->projectAuthenticatedUser->fromUser(user: $user),
            mode                : $issued->mode,
            sessionId           : $issued->sessionId,
            accessTokenId       : $issued->accessToken?->tokenId,
            accessTokenExpiresAt: $issued->accessToken?->expiresAt,
            refreshTokenId      : $issued->refreshToken?->tokenId,
            mfaVerifiedAt       : $issued->mfaVerifiedAt
        );
        $this->currentAuthentication->store(context: $context);

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.federation.login.completed',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'connection_id' => $connection->connectionId,
                                                           'tenant'        => $connection->tenantSlug,
                                                           'user_id'       => $user->getId()->value,
                                                           'risk_action'   => $decision?->action->value,
                                                           'ip_address'    => $data->ipAddress,
                                                           'user_agent'    => $data->userAgent,
                                                       ]
                                       ));

        return AuthenticationResult::success(
            context     : $context,
            accessToken : $issued->accessToken?->token,
            refreshToken: $issued->refreshToken?->token
        );
    }

    /**
     * @throws RandomException
     */
    private function provisionUser(#[SensitiveParameter] string $email, string $displayName) : User
    {
        $username = $this->uniqueUsername(displayName: $displayName, email: $email);

        return 24
                |> random_bytes(...)
                |> bin2hex(...)
                |> $this->passwordHasher(...)
                |> (fn ($x) => User::create(id: new UserId(value: $this->idGenerator->generate()), email: new UserEmail(value: $email), username: $username, passwordHash: $x))
                |> $this->userSource(...);
    }

    private function uniqueUsername(string $displayName, #[SensitiveParameter] string $email) : string
    {
        $normalizedDisplayName = $displayName
                |> trim(...)
                |> strtolower(...)
                |> (static fn ($x) => preg_replace('/[^a-z0-9]+/i', '-', $x));
        $base                  = $normalizedDisplayName !== null && $normalizedDisplayName !== ''
            ? $normalizedDisplayName
            : explode('@', $email)[0];
        $candidate             = trim($base, '-');

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

                if ($role !== null && ! in_array($role, $roles, true)) {
                    $roles[] = $role;
                }
            }
        }

        return $roles;
    }
}
