<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\GroupRoleMappingValidator;
use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\RegisteredScimDirectory;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use Random\RandomException;
use SensitiveParameter;

final readonly class RegisterScimDirectory
{
    private Clock                       $clock;
    private AuditLogInterface           $auditLog;
    private GroupRoleMappingValidator   $groupRoleMappingValidator;
    private PasswordHasher              $passwordHasher;
    private ScimDirectoryStoreInterface $directoryStore;

    public function __construct(
        ScimDirectoryStoreInterface          $directoryStore,
        #[SensitiveParameter] PasswordHasher $passwordHasher,
        GroupRoleMappingValidator            $groupRoleMappingValidator,
        AuditLogInterface                    $auditLog,
        Clock                                $clock
    )
    {
        $this->directoryStore            = $directoryStore;
        $this->passwordHasher            = $passwordHasher;
        $this->groupRoleMappingValidator = $groupRoleMappingValidator;
        $this->auditLog                  = $auditLog;
        $this->clock                     = $clock;
    }

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
        $directory      = new ScimDirectory(
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
                                                           'tenant'       => $directory->tenantSlug,
                                                           'name'         => $directory->name,
                                                       ]
                                       ));

        return new RegisteredScimDirectory(directory: $directory, plainTextToken: $plainTextToken);
    }
}
