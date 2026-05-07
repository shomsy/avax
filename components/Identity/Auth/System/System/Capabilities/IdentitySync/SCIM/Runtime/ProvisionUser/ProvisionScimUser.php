<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser;

use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottleExceeded;
use Avax\Components\Identity\Auth\System\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\UserRole;
use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\Lifecycle\LifecycleSource;
use Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Directories\ScimAccountState;
use Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectory;
use Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryHealth;
use Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Directories\ScimProvisionedIdentity;
use Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Directories\ScimProvisionedIdentityStoreInterface;
use Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Components\Identity\Auth\System\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\System\Foundation\IdGeneratorInterface;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use JsonException;
use Random\RandomException;
use SensitiveParameter;

final readonly class ProvisionScimUser
{
    public function __construct(
        private ProvisionableUserSourceInterface      $provisionableUserSource,
        private ScimDirectoryStoreInterface           $scimDirectoryStore,
        private ScimProvisionedIdentityStoreInterface $scimProvisionedIdentityStore,
        #[SensitiveParameter]
        private PasswordHasher                        $passwordHasher,
        private IdGeneratorInterface                  $idGenerator,
        private AuditLogInterface                     $auditLog,
        private Clock                                 $clock,
        private ?LifecycleOrchestrator                $lifecycleOrchestrator = null,
        private ?AttemptThrottle                      $attemptThrottle = null,
    ) {}

    /**
     * @throws RandomException
     * @throws ScimFailed
     */
    public function execute(ProvisionScimUserData $provisionScimUserData) : ScimProvisioningResult
    {
        $scimDirectory = $this->authenticateDirectory(directoryId: $provisionScimUserData->directoryId, directoryToken: $provisionScimUserData->directoryToken);
        $this->enforceDirectoryAvailability(directory: $scimDirectory);
        $this->enforceThrottle(directoryId: $scimDirectory->directoryId);
        $roles            = $this->resolveRoles(groups: $provisionScimUserData->groups, directory: $scimDirectory);
        $fingerprint      = $this->fingerprint(roles: $roles, data: $provisionScimUserData);
        $existingIdentity = $this->scimProvisionedIdentityStore->find(directoryId: $scimDirectory->directoryId, externalId: $provisionScimUserData->externalId);

        if ($existingIdentity instanceof ScimProvisionedIdentity && $existingIdentity->fingerprint === $fingerprint) {
            return new ScimProvisioningResult(
                userId       : $existingIdentity->userId->value,
                externalId   : $provisionScimUserData->externalId,
                state        : $existingIdentity->state,
                roles        : array_map(callback: static fn (UserRole $userRole) : string => $userRole->value, array: $roles),
                created      : false,
                updated      : false,
                idempotent   : true,
                driftDetected: false,
            );
        }

        $user = $existingIdentity instanceof ScimProvisionedIdentity
            ? $this->provisionableUserSource->findById(id: $existingIdentity->userId)
            : $this->provisionableUserSource->findByEmail(email: $provisionScimUserData->email);

        $created       = false;
        $driftDetected = $existingIdentity instanceof ScimProvisionedIdentity;

        if (! $user instanceof User) {
            $created  = true;
            $password = bin2hex(string: random_bytes(length: 16));
            $user     = $this->provisionableUserSource->create(user: User::create(
                username    : $provisionScimUserData->username,
                passwordHash: $this->passwordHasher->hash(password: $password),
                roles       : $provisionScimUserData->state === ScimAccountState::DISABLED ? [] : $roles,
                permissions : [],
                id          : new UserId(value: $this->idGenerator->generate()),
                email       : new UserEmail(value: $provisionScimUserData->email),
            ));
        }

        $this->provisionableUserSource->updateEmail(email: $provisionScimUserData->email, id: $user->getId());
        $this->provisionableUserSource->replaceRoles(
            roles: $provisionScimUserData->state === ScimAccountState::DISABLED ? [] : $roles,
            id   : $user->getId(),
        );
        $this->provisionableUserSource->replacePermissions(permissions: [], id: $user->getId());

        if ($this->lifecycleOrchestrator instanceof LifecycleOrchestrator) {
            match ($provisionScimUserData->state) {
                ScimAccountState::ACTIVE    => $this->lifecycleOrchestrator->activate(userId: $user->getId(), reason: 'scim_active', source: LifecycleSource::SCIM),
                ScimAccountState::SUSPENDED => $this->lifecycleOrchestrator->suspend(userId: $user->getId(), reason: 'scim_suspended', source: LifecycleSource::SCIM),
                ScimAccountState::DISABLED  => $this->lifecycleOrchestrator->deprovision(userId: $user->getId(), reason: 'scim_disabled', source: LifecycleSource::SCIM),
            };
        } else {
            match ($provisionScimUserData->state) {
                ScimAccountState::ACTIVE                                => $this->provisionableUserSource->activate(id: $user->getId()),
                ScimAccountState::SUSPENDED, ScimAccountState::DISABLED => $this->provisionableUserSource->deactivate(id: $user->getId()),
            };
        }

        $this->scimProvisionedIdentityStore->save(identity: new ScimProvisionedIdentity(
                                                                directoryId   : $scimDirectory->directoryId,
                                                                externalId    : $provisionScimUserData->externalId,
                                                                userId        : $user->getId(),
                                                                fingerprint   : $fingerprint,
                                                                groups        : $provisionScimUserData->groups,
                                                                state         : $provisionScimUserData->state,
                                                                synchronizedAt: $this->clock->now(),
                                                            ));
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.scim.user.provisioned',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'directory_id'   => $scimDirectory->directoryId,
                                                           'tenant'         => $scimDirectory->tenantSlug,
                                                           'external_id'    => $provisionScimUserData->externalId,
                                                           'user_id'        => $user->getId()->value,
                                                           'state'          => $provisionScimUserData->state->value,
                                                           'created'        => $created ? 1 : 0,
                                                           'drift_detected' => $driftDetected ? 1 : 0,
                                                       ],
                                       ));

        return new ScimProvisioningResult(
            userId       : $user->getId()->value,
            externalId   : $provisionScimUserData->externalId,
            state        : $provisionScimUserData->state,
            roles        : array_map(callback: static fn (UserRole $userRole) : string => $userRole->value, array: $roles),
            created      : $created,
            updated      : ! $created,
            idempotent   : false,
            driftDetected: $driftDetected,
        );
    }

    /**
     * @throws ScimFailed
     */
    private function authenticateDirectory(
        string $directoryId,
        #[SensitiveParameter]
        string $directoryToken,
    ) : ScimDirectory
    {
        $directory = $this->scimDirectoryStore->find(directoryId: $directoryId);

        if (! $directory instanceof ScimDirectory) {
            throw ScimFailed::unknownDirectory();
        }

        if (! $this->scimDirectoryStore->verifyToken(directoryId: $directoryId, plainTextToken: $directoryToken)) {
            throw ScimFailed::invalidDirectoryToken();
        }

        return $directory;
    }

    private function enforceDirectoryAvailability(ScimDirectory $scimDirectory) : void
    {
        if ($scimDirectory->health === ScimDirectoryHealth::UNAVAILABLE) {
            throw ScimFailed::serviceUnavailable();
        }
    }

    private function enforceThrottle(string $directoryId) : void
    {
        if (! $this->attemptThrottle instanceof AttemptThrottle) {
            return;
        }

        $key = 'scim:' . $directoryId . ':' . 'provision';

        try {
            $this->attemptThrottle->check(key: $key);
        } catch (AttemptThrottleExceeded $attemptThrottleExceeded) {
            throw ScimFailed::throttled(retryAfterSeconds: $attemptThrottleExceeded->retryAfter(), scope: 'provision');
        }

        $this->attemptThrottle->recordAttempt(key: $key);
    }

    /**
     * @param list<string> $groups
     *
     * @return list<UserRole>
     */
    private function resolveRoles(ScimDirectory $scimDirectory, array $groups) : array
    {
        $roles = [];

        foreach ($groups as $group) {
            foreach ($scimDirectory->groupRoleMap[$group] ?? [] as $roleValue) {
                $role = UserRole::tryFrom(value: $roleValue);
                if ($role === null) {
                    continue;
                }

                if (in_array(needle: $role, haystack: $roles, strict: true)) {
                    continue;
                }

                $roles[] = $role;
            }
        }

        return $roles;
    }

    /**
     * @param list<UserRole> $roles
     */
    private function fingerprint(ProvisionScimUserData $provisionScimUserData, array $roles) : string
    {
        try {
            return hash(algo: 'sha256', data: json_encode(value: [
                                                                     'email' => strtolower(string: trim(string: $provisionScimUserData->email)),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         'username' => trim(string: $provisionScimUserData->username),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             'groups' => $provisionScimUserData->groups,
                                                                     'roles' => array_map(callback: static fn (UserRole $userRole) : string => $userRole->value, array: $roles),
                                                                     'state' => $provisionScimUserData->state->value,
                                                                 ], flags: JSON_THROW_ON_ERROR));
        } catch (JsonException) {
            return hash(algo: 'sha256', data: strtolower(string: trim(string: $provisionScimUserData->email)) . '|' . trim(string: $provisionScimUserData->username) . '|' . $provisionScimUserData->state->value);
        }
    }
}
