<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\RotateToken;

use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\Scim\ScimDirectory;
use Avax\Auth\System\Capability\Scim\ScimDirectoryHealth;
use Avax\Auth\System\Capability\Scim\ScimDirectoryStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Scim\ScimFailed;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\System\Capability\Throttle\AttemptThrottle;
use Avax\Auth\System\Capability\Throttle\AttemptThrottleExceeded;
use Random\RandomException;
use SensitiveParameter;

final readonly class RotateScimToken
{
    public function __construct(
        private ScimDirectoryStoreInterface $directoryStore,
        #[SensitiveParameter] private PasswordHasher $passwordHasher,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private AttemptThrottle|null $attemptThrottle = null
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

        $this->enforceThrottle(directoryId: $directory->directoryId, operation: 'rotate');

        $plainTextToken = bin2hex(random_bytes(24));
        $rotated = new ScimDirectory(
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
                'tenant' => $directory->tenantSlug,
            ]
        ));

        return new RotatedScimToken(directory: $rotated, plainTextToken: $plainTextToken);
    }

    private function enforceThrottle(string $directoryId, string $operation) : void
    {
        if ($this->attemptThrottle === null) {
            return;
        }

        $key = 'scim:' . $directoryId . ':' . $operation;

        try {
            $this->attemptThrottle->check(key: $key);
        } catch (AttemptThrottleExceeded $exceeded) {
            throw ScimFailed::throttled(retryAfterSeconds: $exceeded->retryAfter(), scope: $operation);
        }

        $this->attemptThrottle->recordAttempt(key: $key);
    }
}
