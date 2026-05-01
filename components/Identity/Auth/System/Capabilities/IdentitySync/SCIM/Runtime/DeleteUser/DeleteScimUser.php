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
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryHealth;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimProvisionedIdentityStoreInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class DeleteScimUser
{
    public function __construct(private ProvisionableUserSourceInterface $userSource, private ScimDirectoryStoreInterface $directoryStore, private ScimProvisionedIdentityStoreInterface $identityStore, private AuditLogInterface $auditLog, private Clock $clock, private ?LifecycleOrchestrator $lifecycle = null, private ?AttemptThrottle $attemptThrottle = null) {}

    /**
     * @throws ScimFailed
     */
    public function execute(DeleteScimUserData $data): void
    {
        $directory = $this->authenticateDirectory(directoryId: $data->directoryId, directoryToken: $data->directoryToken);
        $this->enforceDirectoryAvailability(directory: $directory);
        $this->enforceThrottle(directoryId: $directory->directoryId);
        $identity = $this->identityStore->find(directoryId: $directory->directoryId, externalId: $data->externalId);

        if ($identity === null) {
            throw ScimFailed::unknownProvisionedIdentity();
        }

        $this->lifecycle?->deprovision(userId: $identity->userId, source: LifecycleSource::SCIM, reason: 'scim_deleted');
        if ($this->lifecycle === null) {
            $this->userSource->deactivate(id: $identity->userId);
            $this->userSource->replaceRoles(id: $identity->userId, roles: []);
            $this->userSource->replacePermissions(id: $identity->userId, permissions: []);
        }
        $this->identityStore->remove(directoryId: $directory->directoryId, externalId: $data->externalId);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.scim.user.deleted',
            occurredAt: $this->clock->now(),
            context   : [
                'directory_id' => $directory->directoryId,
                'tenant'      => $directory->tenantSlug,
                'external_id' => $data->externalId,
                'user_id'     => $identity->userId->value,
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
    ): ScimDirectory {
        $directory = $this->directoryStore->find(directoryId: $directoryId);

        if ($directory === null) {
            throw ScimFailed::unknownDirectory();
        }

        if (! $this->directoryStore->verifyToken(directoryId: $directoryId, plainTextToken: $directoryToken)) {
            throw ScimFailed::invalidDirectoryToken();
        }

        return $directory;
    }

    private function enforceDirectoryAvailability(ScimDirectory $directory): void
    {
        if ($directory->health === ScimDirectoryHealth::UNAVAILABLE) {
            throw ScimFailed::serviceUnavailable();
        }
    }

    private function enforceThrottle(string $directoryId): void
    {
        if ($this->attemptThrottle === null) {
            return;
        }

        $key = 'scim:' . $directoryId . ':' . 'delete';

        try {
            $this->attemptThrottle->check(key: $key);
        } catch (AttemptThrottleExceeded $exceeded) {
            throw ScimFailed::throttled(retryAfterSeconds: $exceeded->retryAfter(), scope: 'delete');
        }

        $this->attemptThrottle->recordAttempt(key: $key);
    }
}
