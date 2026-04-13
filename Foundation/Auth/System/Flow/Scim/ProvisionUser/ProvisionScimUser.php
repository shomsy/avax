<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\ProvisionUser;

use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\Scim\ScimAccountState;
use Avax\Auth\System\Capability\Scim\ScimDirectory;
use Avax\Auth\System\Capability\Scim\ScimDirectoryStoreInterface;
use Avax\Auth\System\Capability\Scim\ScimProvisionedIdentity;
use Avax\Auth\System\Capability\Scim\ScimProvisionedIdentityStoreInterface;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Capability\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Scim\ScimFailed;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use JsonException;
use SensitiveParameter;

final readonly class ProvisionScimUser
{
    public function __construct(
        private ProvisionableUserSourceInterface $userSource,
        private ScimDirectoryStoreInterface $directoryStore,
        private ScimProvisionedIdentityStoreInterface $identityStore,
        #[SensitiveParameter] private PasswordHasher $passwordHasher,
        private IdGeneratorInterface $idGenerator,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws ScimFailed
     */
    public function execute(ProvisionScimUserData $data) : ScimProvisioningResult
    {
        $directory = $this->authenticateDirectory(directoryId: $data->directoryId, directoryToken: $data->directoryToken);
        $roles = $this->resolveRoles(directory: $directory, groups: $data->groups);
        $fingerprint = $this->fingerprint(data: $data, roles: $roles);
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

        $created = false;
        $driftDetected = $existingIdentity !== null;

        if ($user === null) {
            $created = true;
            $user = $this->userSource->create(user: User::create(
                id          : new UserId(value: $this->idGenerator->generate()),
                email       : new UserEmail(value: $data->email),
                username    : $data->username,
                passwordHash: $this->passwordHasher->hash(password: bin2hex(random_bytes(16))),
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

        match ($data->state) {
            ScimAccountState::ACTIVE => $this->userSource->activate(id: $user->getId()),
            ScimAccountState::SUSPENDED, ScimAccountState::DISABLED => $this->userSource->deactivate(id: $user->getId()),
        };

        $this->identityStore->save(identity: new ScimProvisionedIdentity(
            directoryId     : $directory->directoryId,
            externalId      : $data->externalId,
            userId          : $user->getId(),
            fingerprint     : $fingerprint,
            groups          : array_values($data->groups),
            state           : $data->state,
            synchronizedAt  : $this->clock->now()
        ));
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.scim.user.provisioned',
            occurredAt: $this->clock->now(),
            context   : [
                'directory_id' => $directory->directoryId,
                'tenant' => $directory->tenantSlug,
                'external_id' => $data->externalId,
                'user_id' => $user->getId()->value,
                'state' => $data->state->value,
                'created' => $created ? 1 : 0,
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

    /**
     * @param list<string> $groups
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
     * @throws ScimFailed
     */
    private function authenticateDirectory(
        string $directoryId,
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
}
