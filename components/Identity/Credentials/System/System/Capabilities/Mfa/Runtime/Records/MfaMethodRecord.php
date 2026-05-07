<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\System\Capabilities\Mfa\Runtime\Records;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Mfa\Runtime\Backup\BackupCodeRecord;
use Avax\Components\Identity\Credentials\System\System\Capabilities\Mfa\Runtime\Enums\MfaMethod;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored active MFA method and recovery state.
 *
 * @param list<BackupCodeRecord> $backupCodes
 */
final readonly class MfaMethodRecord
{
    /** @var list<BackupCodeRecord> */
    public array $backupCodes;

    /**
     * @param list<BackupCodeRecord> $backupCodes
     */
    public function __construct(
        public UserId            $userId,
        public MfaMethod         $method,
        #[SensitiveParameter]
        public string            $secret,
        public DateTimeImmutable $enabledAt,
        #[SensitiveParameter]
        ?array                   $backupCodes = null,
        public ?int              $lastAcceptedTimeStep = null,
    )
    {
        $backupCodes       ??= [];
        $this->backupCodes = $backupCodes;
    }

    /**
     * @param list<BackupCodeRecord> $backupCodes
     */
    public function withBackupCodes(#[SensitiveParameter] array $backupCodes) : self
    {
        return new self(
            userId              : $this->userId,
            method              : $this->method,
            secret              : $this->secret,
            enabledAt           : $this->enabledAt,
            backupCodes         : $backupCodes,
            lastAcceptedTimeStep: $this->lastAcceptedTimeStep,
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
            lastAcceptedTimeStep: $timeStep,
        );
    }
}
