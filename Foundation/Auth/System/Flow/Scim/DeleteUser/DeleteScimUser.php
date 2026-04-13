<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\DeleteUser;

use Avax\Auth\System\Capability\Scim\ScimDirectoryStoreInterface;
use Avax\Auth\System\Capability\Scim\ScimProvisionedIdentityStoreInterface;
use Avax\Auth\System\Capability\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Scim\ScimFailed;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class DeleteScimUser
{
    public function __construct(
        private ProvisionableUserSourceInterface $userSource,
        private ScimDirectoryStoreInterface $directoryStore,
        private ScimProvisionedIdentityStoreInterface $identityStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws ScimFailed
     */
    public function execute(DeleteScimUserData $data) : void
    {
        $directory = $this->authenticateDirectory($data->directoryId, $data->directoryToken);
        $identity = $this->identityStore->find($directory->directoryId, $data->externalId);

        if ($identity === null) {
            throw ScimFailed::unknownProvisionedIdentity();
        }

        $this->userSource->deactivate($identity->userId);
        $this->userSource->replaceRoles($identity->userId, []);
        $this->userSource->replacePermissions($identity->userId, []);
        $this->identityStore->remove($directory->directoryId, $data->externalId);
        $this->auditLog->record(new AuditEvent(
            name      : 'auth.scim.user.deleted',
            occurredAt: $this->clock->now(),
            context   : [
                'directory_id' => $directory->directoryId,
                'tenant' => $directory->tenantSlug,
                'external_id' => $data->externalId,
                'user_id' => $identity->userId->value,
            ]
        ));
    }

    /**
     * @throws ScimFailed
     */
    private function authenticateDirectory(
        string $directoryId,
        #[SensitiveParameter] string $directoryToken
    ) : \Avax\Auth\System\Capability\Scim\ScimDirectory
    {
        $directory = $this->directoryStore->find($directoryId);

        if ($directory === null) {
            throw ScimFailed::unknownDirectory();
        }

        if (! $this->directoryStore->verifyToken($directoryId, $directoryToken)) {
            throw ScimFailed::invalidDirectoryToken();
        }

        return $directory;
    }
}
