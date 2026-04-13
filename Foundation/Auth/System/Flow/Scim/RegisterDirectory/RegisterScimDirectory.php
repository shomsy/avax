<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\RegisterDirectory;

use Avax\Auth\System\Capability\Federation\GroupRoleMappingValidator;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\Scim\RegisteredScimDirectory;
use Avax\Auth\System\Capability\Scim\ScimDirectory;
use Avax\Auth\System\Capability\Scim\ScimDirectoryStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Scim\ScimFailed;
use Avax\Auth\System\Foundation\Clock;
use Random\RandomException;
use SensitiveParameter;

final readonly class RegisterScimDirectory
{
    public function __construct(
        private ScimDirectoryStoreInterface $directoryStore,
        #[SensitiveParameter] private PasswordHasher $passwordHasher,
        private GroupRoleMappingValidator $groupRoleMappingValidator,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws ScimFailed
     * @throws RandomException
     * @throws RandomException
     */
    public function execute(RegisterScimDirectoryData $data) : RegisteredScimDirectory
    {
        if (! $this->groupRoleMappingValidator->isValid(groupRoleMap: $data->groupRoleMap)) {
            throw ScimFailed::invalidGroupRoleMapping();
        }

        $plainTextToken = bin2hex(random_bytes(24));
        $directory = new ScimDirectory(
            directoryId : 'scim_' . bin2hex(random_bytes(12)),
            tenantSlug  : trim($data->tenantSlug),
            name        : trim($data->name),
            tokenHash   : $this->passwordHasher->hash(password: $plainTextToken),
            groupRoleMap: $data->groupRoleMap,
            createdAt   : $this->clock->now()
        );

        $this->directoryStore->save(directory: $directory);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.scim.directory.registered',
            occurredAt: $this->clock->now(),
            context   : [
                'directory_id' => $directory->directoryId,
                'tenant' => $directory->tenantSlug,
                'name' => $directory->name,
            ]
        ));

        return new RegisteredScimDirectory(directory: $directory, plainTextToken: $plainTextToken);
    }
}
