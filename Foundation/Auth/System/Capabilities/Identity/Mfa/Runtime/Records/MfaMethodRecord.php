<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Records;

use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\BackupCodeRecord;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaMethod;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored active MFA method and recovery state.
 *
 * @param list<BackupCodeRecord> $backupCodes
 */
final readonly class MfaMethodRecord
{
    public int|null $lastAcceptedTimeStep;
    /** @var list<BackupCodeRecord> */
    public array             $backupCodes;
    public DateTimeImmutable $enabledAt;
    public string            $secret;
    public MfaMethod         $method;
    public UserId            $userId;

    /**
     * @param list<BackupCodeRecord> $backupCodes
     */
    public function __construct(
        UserId                           $userId,
        MfaMethod                        $method,
        #[SensitiveParameter] string     $secret,
        DateTimeImmutable                $enabledAt,
        #[SensitiveParameter] array|null $backupCodes = null,
        int|null                         $lastAcceptedTimeStep = null
    )
    {
        $backupCodes                ??= [];
        $this->userId               = $userId;
        $this->method               = $method;
        $this->secret               = $secret;
        $this->enabledAt            = $enabledAt;
        $this->backupCodes          = $backupCodes;
        $this->lastAcceptedTimeStep = $lastAcceptedTimeStep;
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
