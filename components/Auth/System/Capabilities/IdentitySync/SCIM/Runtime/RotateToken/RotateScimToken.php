<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken;

use Avax\Auth\System\Capabilities\Access\Authentication\Throttle\AttemptThrottle;
use Avax\Auth\System\Capabilities\Access\Authentication\Throttle\AttemptThrottleExceeded;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryHealth;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use Random\RandomException;
use SensitiveParameter;

final readonly class RotateScimToken
{
    public function __construct(
        private ScimDirectoryStoreInterface          $directoryStore,
        #[SensitiveParameter] private PasswordHasher $passwordHasher,
        private AuditLogInterface                    $auditLog,
        private Clock                                $clock,
        private AttemptThrottle|null                 $attemptThrottle = null
    ) {}

    /**
     * @throws ScimFailed
     * @throws RandomException
     */
    public function execute(string $directoryId) : RotatedScimToken
    {
        $directory = $this->directoryStore->find(directoryId: $directoryId);

        if ($directory === null) {
            throw ScimFailed::unknownDirectory();
        }

        if ($directory->health === ScimDirectoryHealth::UNAVAILABLE) {
            throw ScimFailed::serviceUnavailable();
        }

        $this->enforceThrottle(directoryId: $directory->directoryId);

        $plainTextToken = bin2hex(string: random_bytes(length: 24));
        $rotated        = new ScimDirectory(
            directoryId : $directory->directoryId,
            tenantSlug  : $directory->tenantSlug,
            name        : $directory->name,
            tokenHash   : $this->passwordHasher->hash(password: $plainTextToken),
            groupRoleMap: $directory->groupRoleMap,
            createdAt   : $directory->createdAt,
            rotatedAt   : $this->clock->now()
        );

        $this->directoryStore->save(directory: $rotated);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.scim.directory.token_rotated',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'directory_id' => $directory->directoryId,
                                                           'tenant'       => $directory->tenantSlug,
                                                       ]
                                       ));

        return new RotatedScimToken(directory: $rotated, plainTextToken: $plainTextToken);
    }

    private function enforceThrottle(string $directoryId) : void
    {
        if ($this->attemptThrottle === null) {
            return;
        }

        $key = 'scim:' . $directoryId . ':' . 'rotate';

        try {
            $this->attemptThrottle->check(key: $key);
        } catch (AttemptThrottleExceeded $exceeded) {
            throw ScimFailed::throttled(retryAfterSeconds: $exceeded->retryAfter(), scope: 'rotate');
        }

        $this->attemptThrottle->recordAttempt(key: $key);
    }
}
