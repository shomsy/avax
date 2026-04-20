<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Scim\RotateToken;

use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Scim\ScimDirectory;
use Avax\Auth\System\Capabilities\Scim\ScimDirectoryHealth;
use Avax\Auth\System\Capabilities\Scim\ScimDirectoryStoreInterface;
use Avax\Auth\System\Capabilities\Throttle\AttemptThrottle;
use Avax\Auth\System\Capabilities\Throttle\AttemptThrottleExceeded;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\Scim\ScimFailed;
use Avax\Auth\System\Foundation\Clock;
use Random\RandomException;
use SensitiveParameter;

final readonly class RotateScimToken
{
    private AttemptThrottle|null        $attemptThrottle;
    private Clock                       $clock;
    private AuditLogInterface           $auditLog;
    private PasswordHasher              $passwordHasher;
    private ScimDirectoryStoreInterface $directoryStore;

    public function __construct(
        ScimDirectoryStoreInterface          $directoryStore,
        #[SensitiveParameter] PasswordHasher $passwordHasher,
        AuditLogInterface                    $auditLog,
        Clock                                $clock,
        AttemptThrottle|null                 $attemptThrottle = null
    )
    {
        $this->directoryStore  = $directoryStore;
        $this->passwordHasher  = $passwordHasher;
        $this->auditLog        = $auditLog;
        $this->clock           = $clock;
        $this->attemptThrottle = $attemptThrottle;
    }

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

        $plainTextToken = bin2hex(random_bytes(24));
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
