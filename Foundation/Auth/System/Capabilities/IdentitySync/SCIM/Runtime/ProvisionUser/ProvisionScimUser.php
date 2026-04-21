<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser;

use Avax\Auth\System\Capabilities\Access\Authentication\Throttle\AttemptThrottle;
use Avax\Auth\System\Capabilities\Access\Authentication\Throttle\AttemptThrottleExceeded;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleSource;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimAccountState;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryHealth;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryStoreInterface;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimProvisionedIdentity;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimProvisionedIdentityStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use JsonException;
use SensitiveParameter;

final readonly class ProvisionScimUser
{
    private AttemptThrottle|null                  $attemptThrottle;
    private LifecycleOrchestrator|null            $lifecycle;
    private Clock                                 $clock;
    private AuditLogInterface                     $auditLog;
    private IdGeneratorInterface                  $idGenerator;
    private PasswordHasher                        $passwordHasher;
    private ScimProvisionedIdentityStoreInterface $identityStore;
    private ScimDirectoryStoreInterface           $directoryStore;
    private ProvisionableUserSourceInterface      $userSource;

    public function __construct(
        ProvisionableUserSourceInterface      $userSource,
        ScimDirectoryStoreInterface           $directoryStore,
        ScimProvisionedIdentityStoreInterface $identityStore,
        #[SensitiveParameter] PasswordHasher  $passwordHasher,
        IdGeneratorInterface                  $idGenerator,
        AuditLogInterface                     $auditLog,
        Clock                                 $clock,
        LifecycleOrchestrator|null            $lifecycle = null,
        AttemptThrottle|null                  $attemptThrottle = null
    )
    {
        $this->userSource      = $userSource;
        $this->directoryStore  = $directoryStore;
        $this->identityStore   = $identityStore;
        $this->passwordHasher  = $passwordHasher;
        $this->idGenerator     = $idGenerator;
        $this->auditLog        = $auditLog;
        $this->clock           = $clock;
        $this->lifecycle       = $lifecycle;
        $this->attemptThrottle = $attemptThrottle;
    }

    /**
     * @param ProvisionScimUserData $data
     *
     * @return ScimProvisioningResult
     */
    public function execute(ProvisionScimUserData $data) : ScimProvisioningResult
    {
        $directory = $this->authenticateDirectory(directoryId: $data->directoryId, directoryToken: $data->directoryToken);
        $this->enforceDirectoryAvailability(directory: $directory);
        $this->enforceThrottle(directoryId: $directory->directoryId);
        $roles            = $this->resolveRoles(directory: $directory, groups: $data->groups);
        $fingerprint      = $this->fingerprint(data: $data, roles: $roles);
        $existingIdentity = $this->identityStore->find(directoryId: $directory->directoryId, externalId: $data->externalId);

        if ($existingIdentity !== null && $existingIdentity->fingerprint === $fingerprint) {
            return new ScimProvisioningResult(
                userId       : $existingIdentity->userId->value,
                externalId   : $data->externalId,
                state        : $existingIdentity->state,
                roles        : array_map(static fn (UserRole $role) : string => $role->value, $roles),
                created      : false,
                updated      : false,
                idempotent   : true,
                driftDetected: false
            );
        }

        $user = $existingIdentity !== null
            ? $this->userSource->findById(id: $existingIdentity->userId)
            : $this->userSource->findByEmail(email: $data->email);

        $created       = false;
        $driftDetected = $existingIdentity !== null;

        if ($user === null) {
            $created = true;
            $password = bin2hex(random_bytes(16));
            $user     = $this->userSource->create(user: User::create(
                id          : new UserId(value: $this->idGenerator->generate()),
                email       : new UserEmail(value: $data->email),
                username    : $data->username,
                passwordHash: $this->passwordHasher->hash(password: $password),
                roles       : $data->state === ScimAccountState::DISABLED ? [] : $roles,
                permissions : []
            ));
        }

        $this->userSource->updateEmail(id: $user->getId(), email: $data->email);
        $this->userSource->replaceRoles(
            id   : $user->getId(),
            roles: $data->state === ScimAccountState::DISABLED ? [] : $roles
        );
        $this->userSource->replacePermissions(id: $user->getId(), permissions: []);

        if ($this->lifecycle !== null) {
            match ($data->state) {
                ScimAccountState::ACTIVE    => $this->lifecycle->activate(userId: $user->getId(), source: LifecycleSource::SCIM, reason: 'scim_active'),
                ScimAccountState::SUSPENDED => $this->lifecycle->suspend(userId: $user->getId(), source: LifecycleSource::SCIM, reason: 'scim_suspended'),
                ScimAccountState::DISABLED  => $this->lifecycle->deprovision(userId: $user->getId(), source: LifecycleSource::SCIM, reason: 'scim_disabled'),
            };
        } else {
            match ($data->state) {
                ScimAccountState::ACTIVE                                => $this->userSource->activate(id: $user->getId()),
                ScimAccountState::SUSPENDED, ScimAccountState::DISABLED => $this->userSource->deactivate(id: $user->getId()),
            };
        }

        $this->identityStore->save(identity: new ScimProvisionedIdentity(
                                                 directoryId   : $directory->directoryId,
                                                 externalId    : $data->externalId,
                                                 userId        : $user->getId(),
                                                 fingerprint   : $fingerprint,
                                                 groups        : array_values($data->groups),
                                                 state         : $data->state,
                                                 synchronizedAt: $this->clock->now()
                                             ));
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.scim.user.provisioned',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'directory_id'   => $directory->directoryId,
                                                           'tenant'         => $directory->tenantSlug,
                                                           'external_id'    => $data->externalId,
                                                           'user_id'        => $user->getId()->value,
                                                           'state'          => $data->state->value,
                                                           'created'        => $created ? 1 : 0,
                                                           'drift_detected' => $driftDetected ? 1 : 0,
                                                       ]
                                       ));

        return new ScimProvisioningResult(
            userId       : $user->getId()->value,
            externalId   : $data->externalId,
            state        : $data->state,
            roles        : array_map(static fn (UserRole $role) : string => $role->value, $roles),
            created      : $created,
            updated      : ! $created,
            idempotent   : false,
            driftDetected: $driftDetected
        );
    }

    /**
     * @throws ScimFailed
     */
    private function authenticateDirectory(
        string                       $directoryId,
        #[SensitiveParameter] string $directoryToken
    ) : ScimDirectory
    {
        $directory = $this->directoryStore->find(directoryId: $directoryId);

        if ($directory === null) {
            throw ScimFailed::unknownDirectory();
        }

        if (! $this->directoryStore->verifyToken(directoryId: $directoryId, plainTextToken: $directoryToken)) {
            throw ScimFailed::invalidDirectoryToken();
        }

        return $directory;
    }

    private function enforceDirectoryAvailability(ScimDirectory $directory) : void
    {
        if ($directory->health === ScimDirectoryHealth::UNAVAILABLE) {
            throw ScimFailed::serviceUnavailable();
        }
    }

    private function enforceThrottle(string $directoryId) : void
    {
        if ($this->attemptThrottle === null) {
            return;
        }

        $key = 'scim:' . $directoryId . ':' . 'provision';

        try {
            $this->attemptThrottle->check(key: $key);
        } catch (AttemptThrottleExceeded $exceeded) {
            throw ScimFailed::throttled(retryAfterSeconds: $exceeded->retryAfter(), scope: 'provision');
        }

        $this->attemptThrottle->recordAttempt(key: $key);
    }

    /**
     * @param list<string> $groups
     *
     * @return list<UserRole>
     */
    private function resolveRoles(ScimDirectory $directory, array $groups) : array
    {
        $roles = [];

        foreach ($groups as $group) {
            foreach ($directory->groupRoleMap[$group] ?? [] as $roleValue) {
                $role = UserRole::tryFrom(value: $roleValue);

                if ($role === null || in_array($role, $roles, true)) {
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
    private function fingerprint(ProvisionScimUserData $data, array $roles) : string
    {
        try {
            return hash('sha256', json_encode([
                                                  'email' => strtolower(trim($data->email)),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         'username' => trim($data->username),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      'groups' => array_values($data->groups),
                                                  'roles' => array_map(static fn (UserRole $role) : string => $role->value, $roles),
                                                  'state' => $data->state->value,
                                              ], JSON_THROW_ON_ERROR));
        } catch (JsonException) {
            return hash('sha256', strtolower(trim($data->email)) . '|' . trim($data->username) . '|' . $data->state->value);
        }
    }
}
