<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\RegisteredScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\GroupRoleMappingValidator;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use Random\RandomException;
use SensitiveParameter;

final readonly class RegisterScimDirectory
{
    public function __construct(
        private ScimDirectoryStoreInterface $scimDirectoryStore,
        #[SensitiveParameter]
        private PasswordHasher $passwordHasher,
        private GroupRoleMappingValidator $groupRoleMappingValidator,
        private AuditLogInterface $auditLog,
        private Clock $clock,
    ) {}

    /**
     * @throws ScimFailed
     * @throws RandomException
     */
    public function execute(RegisterScimDirectoryData $registerScimDirectoryData) : RegisteredScimDirectory
    {
        if (! $this->groupRoleMappingValidator->isValid(groupRoleMap: $registerScimDirectoryData->groupRoleMap)) {
            throw ScimFailed::invalidGroupRoleMapping();
        }

        $plainTextToken = bin2hex(string: random_bytes(length: 24));
        $scimDirectory = new ScimDirectory(
            directoryId : 'scim_' . bin2hex(string: random_bytes(length: 12)),
            tenantSlug  : trim(string: $registerScimDirectoryData->tenantSlug),
            name        : trim(string: $registerScimDirectoryData->name),
            tokenHash   : $this->passwordHasher->hash(password: $plainTextToken),
            groupRoleMap: $registerScimDirectoryData->groupRoleMap,
            createdAt   : $this->clock->now(),
        );

        $this->scimDirectoryStore->save(directory: $scimDirectory);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.scim.directory.registered',
            occurredAt: $this->clock->now(),
            context   : [
                            'directory_id' => $scimDirectory->directoryId,
                            'tenant'       => $scimDirectory->tenantSlug,
                            'name'         => $scimDirectory->name,
            ],
        ));

        return new RegisteredScimDirectory(directory: $scimDirectory, plainTextToken: $plainTextToken);
    }
}
