<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser;

use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottleExceeded;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleSource;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryHealth;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimProvisionedIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimProvisionedIdentityStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class DeleteScimUser
{
    public function __construct(private ProvisionableUserSourceInterface $provisionableUserSource, private ScimDirectoryStoreInterface $scimDirectoryStore, private ScimProvisionedIdentityStoreInterface $scimProvisionedIdentityStore, private AuditLogInterface $auditLog, private Clock $clock, private LifecycleOrchestrator|null $lifecycleOrchestrator = null, private AttemptThrottle|null $attemptThrottle = null) {}

    /**
     * @throws ScimFailed
     */
    public function execute(DeleteScimUserData $deleteScimUserData) : void
    {
        $scimDirectory = $this->authenticateDirectory(directoryId: $deleteScimUserData->directoryId, directoryToken: $deleteScimUserData->directoryToken);
        $this->enforceDirectoryAvailability(directory: $scimDirectory);
        $this->enforceThrottle(directoryId: $scimDirectory->directoryId);
        $identity = $this->scimProvisionedIdentityStore->find(directoryId: $scimDirectory->directoryId, externalId: $deleteScimUserData->externalId);

        if (! $identity instanceof ScimProvisionedIdentity) {
            throw ScimFailed::unknownProvisionedIdentity();
        }

        $this->lifecycleOrchestrator?->deprovision(userId: $identity->userId, source: LifecycleSource::SCIM, reason: 'scim_deleted');
        if (! $this->lifecycleOrchestrator instanceof LifecycleOrchestrator) {
            $this->provisionableUserSource->deactivate(id: $identity->userId);
            $this->provisionableUserSource->replaceRoles(roles: [], id: $identity->userId);
            $this->provisionableUserSource->replacePermissions(permissions: [], id: $identity->userId);
        }

        $this->scimProvisionedIdentityStore->remove(directoryId: $scimDirectory->directoryId, externalId: $deleteScimUserData->externalId);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.scim.user.deleted',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'directory_id' => $scimDirectory->directoryId,
                                                           'tenant'       => $scimDirectory->tenantSlug,
                                                           'external_id'  => $deleteScimUserData->externalId,
                                                           'user_id'      => $identity->userId->value,
                                                       ],
                                       ));
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

        $key = 'scim:' . $directoryId . ':' . 'delete';

        try {
            $this->attemptThrottle->check(key: $key);
        } catch (AttemptThrottleExceeded $attemptThrottleExceeded) {
            throw ScimFailed::throttled(retryAfterSeconds: $attemptThrottleExceeded->retryAfter(), scope: 'delete');
        }

        $this->attemptThrottle->recordAttempt(key: $key);
    }
}
