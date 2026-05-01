<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport\GroupRoleMappingValidator;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\RegisteredScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Random\RandomException;
use SensitiveParameter;

final readonly class RegisterScimDirectory
{
    public function __construct(
        private ScimDirectoryStoreInterface $directoryStore,
        #[SensitiveParameter]
        private PasswordHasher              $passwordHasher,
        private GroupRoleMappingValidator   $groupRoleMappingValidator,
        private AuditLogInterface           $auditLog,
        private Clock                       $clock,
    ) {}

    /**
     * @throws ScimFailed
     * @throws RandomException
     */
    public function execute(RegisterScimDirectoryData $data) : RegisteredScimDirectory
    {
        if (! $this->groupRoleMappingValidator->isValid(groupRoleMap: $data->groupRoleMap)) {
            throw ScimFailed::invalidGroupRoleMapping();
        }

        $plainTextToken = bin2hex(string: random_bytes(length: 24));
        $directory      = new ScimDirectory(
            directoryId : 'scim_' . bin2hex(string: random_bytes(length: 12)),
            tenantSlug  : trim(string: $data->tenantSlug),
            name        : trim(string: $data->name),
            tokenHash   : $this->passwordHasher->hash(password: $plainTextToken),
            groupRoleMap: $data->groupRoleMap,
            createdAt   : $this->clock->now(),
        );

        $this->directoryStore->save(directory: $directory);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.scim.directory.registered',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'directory_id' => $directory->directoryId,
                                                           'tenant'       => $directory->tenantSlug,
                                                           'name'         => $directory->name,
                                                       ],
                                       ));

        return new RegisteredScimDirectory(directory: $directory, plainTextToken: $plainTextToken);
    }
}
