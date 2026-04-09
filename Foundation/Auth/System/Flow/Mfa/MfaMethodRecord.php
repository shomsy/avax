<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;

/**
 * Stored active MFA method and recovery state.
 *
 * @param list<BackupCodeRecord> $backupCodes
 */
final readonly class MfaMethodRecord
{
    /**
     * @param list<BackupCodeRecord> $backupCodes
     */
    public function __construct(
        public UserId            $userId,
        public MfaMethod         $method,
        public string            $secret,
        public DateTimeImmutable $enabledAt,
        public array             $backupCodes = [],
        public int|null          $lastAcceptedTimeStep = null
    ) {}

    /**
     * @param list<BackupCodeRecord> $backupCodes
     */
    public function withBackupCodes(array $backupCodes) : self
    {
        return new self(
            userId              : $this->userId,
            method              : $this->method,
            secret              : $this->secret,
            enabledAt           : $this->enabledAt,
            backupCodes         : $backupCodes,
            lastAcceptedTimeStep: $this->lastAcceptedTimeStep
        );
    }

    public function withLastAcceptedTimeStep(int $timeStep) : self
    {
        return new self(
            userId              : $this->userId,
            method              : $this->method,
            secret              : $this->secret,
            enabledAt           : $this->enabledAt,
            backupCodes         : $this->backupCodes,
            lastAcceptedTimeStep: $timeStep
        );
    }
}
