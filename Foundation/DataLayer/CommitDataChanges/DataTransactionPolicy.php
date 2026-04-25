<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

enum DataTransactionPolicy: string
{
    case AUTO_COMMIT = 'auto_commit';
    case MANUAL      = 'manual';
    case IMMEDIATE   = 'immediate';
    case DEFERRED    = 'deferred';
    case SAVEPOINT   = 'savepoint';

    public function supportsSavepoints() : bool
    {
        return $this === self::SAVEPOINT;
    }

    public function autoCommits() : bool
    {
        return $this === self::AUTO_COMMIT;
    }
}

enum TwoPhaseCommitPolicy: string
{
    case DISABLED = 'disabled';
    case OPTIONAL = 'optional';
    case REQUIRED = 'required';
    case ALWAYS   = 'always';
}

final readonly class DataTransactionPolicy
{
    public self                 $policy;
    public IsolationLevel       $defaultIsolation;
    public int|null             $timeoutSeconds;
    public int|null             $maxRetries;
    public int                  $retryDelayMs;
    public bool                  $allowDeadlockRetries;
    public bool                  $checkForeignKeys;
    public TwoPhaseCommitPolicy $twoPhaseCommit;
    public int|null             $maxAffectedRows;

    private function __construct(
        self     $policy,
        IsolationLevel|null       $defaultIsolation = null,
        int|null $timeoutSeconds = null,
        int|null $maxRetries = null,
        int|null                  $retryDelayMs = null,
        bool|null                 $allowDeadlockRetries = null,
        bool|null                 $checkForeignKeys = null,
        TwoPhaseCommitPolicy|null $twoPhaseCommit = null,
        int|null $maxAffectedRows = null
    )
    {
        $defaultIsolation     ??= IsolationLevel::REPEATABLE_READ;
        $retryDelayMs         ??= 100;
        $allowDeadlockRetries ??= true;
        $checkForeignKeys     ??= true;
        $twoPhaseCommit       ??= TwoPhaseCommitPolicy::DISABLED;
        $this->policy               = $policy;
        $this->defaultIsolation     = $defaultIsolation;
        $this->timeoutSeconds       = $timeoutSeconds;
        $this->maxRetries           = $maxRetries;
        $this->retryDelayMs         = $retryDelayMs;
        $this->allowDeadlockRetries = $allowDeadlockRetries;
        $this->checkForeignKeys     = $checkForeignKeys;
        $this->twoPhaseCommit       = $twoPhaseCommit;
        $this->maxAffectedRows      = $maxAffectedRows;
    }

    public static function strict() : self
    {
        return new self(
            policy              : self::MANUAL,
            timeoutSeconds      : 30,
            maxRetries          : 3,
            retryDelayMs        : 100,
            allowDeadlockRetries: true,
            checkForeignKeys    : true,
            maxAffectedRows     : 10000
        );
    }

    public static function relaxed() : self
    {
        return new self(
            policy              : self::AUTO_COMMIT,
            defaultIsolation    : IsolationLevel::READ_COMMITTED,
            timeoutSeconds      : 60,
            allowDeadlockRetries: false,
            checkForeignKeys    : false
        );
    }

    public static function batch() : self
    {
        return new self(
            policy              : self::MANUAL,
            defaultIsolation    : IsolationLevel::REPEATABLE_READ,
            timeoutSeconds      : 300,
            maxRetries          : 1,
            allowDeadlockRetries: true,
            checkForeignKeys    : true,
            maxAffectedRows     : 100000
        );
    }

    public static function create(array $options = []) : self
    {
        return new self(
            policy              : self::from($options['policy'] ?? 'manual'),
            defaultIsolation    : IsolationLevel::from(value: $options['isolation'] ?? 'REPEATABLE READ'),
            timeoutSeconds      : $options['timeout'] ?? null,
            maxRetries          : $options['max_retries'] ?? null,
            retryDelayMs        : $options['retry_delay'] ?? 100,
            allowDeadlockRetries: $options['deadlock_retries'] ?? true,
            checkForeignKeys    : $options['foreign_keys'] ?? true,
            twoPhaseCommit      : TwoPhaseCommitPolicy::from(value: $options['two_phase'] ?? 'disabled'),
            maxAffectedRows     : $options['max_affected'] ?? null
        );
    }

    public function withIsolation(IsolationLevel $isolation) : self
    {
        return new self(
            policy              : $this->policy,
            defaultIsolation    : $isolation,
            timeoutSeconds      : $this->timeoutSeconds,
            maxRetries          : $this->maxRetries,
            retryDelayMs        : $this->retryDelayMs,
            allowDeadlockRetries: $this->allowDeadlockRetries,
            checkForeignKeys    : $this->checkForeignKeys,
            twoPhaseCommit      : $this->twoPhaseCommit,
            maxAffectedRows     : $this->maxAffectedRows
        );
    }

    public function withTimeout(int $seconds) : self
    {
        return new self(
            policy              : $this->policy,
            defaultIsolation    : $this->defaultIsolation,
            timeoutSeconds      : $seconds,
            maxRetries          : $this->maxRetries,
            retryDelayMs        : $this->retryDelayMs,
            allowDeadlockRetries: $this->allowDeadlockRetries,
            checkForeignKeys    : $this->checkForeignKeys,
            twoPhaseCommit      : $this->twoPhaseCommit,
            maxAffectedRows     : $this->maxAffectedRows
        );
    }

    public function withMaxRetries(int $retries) : self
    {
        return new self(
            policy              : $this->policy,
            defaultIsolation    : $this->defaultIsolation,
            timeoutSeconds      : $this->timeoutSeconds,
            maxRetries          : $retries,
            retryDelayMs        : $this->retryDelayMs,
            allowDeadlockRetries: $this->allowDeadlockRetries,
            checkForeignKeys    : $this->checkForeignKeys,
            twoPhaseCommit      : $this->twoPhaseCommit,
            maxAffectedRows     : $this->maxAffectedRows
        );
    }

    public function isSerializable() : bool
    {
        return $this->defaultIsolation === IsolationLevel::SERIALIZABLE;
    }

    public function toArray() : array
    {
        return [
            'policy'           => $this->policy->value,
            'isolation'        => $this->defaultIsolation->value,
            'timeout'          => $this->timeoutSeconds,
            'max_retries'      => $this->maxRetries,
            'retry_delay'      => $this->retryDelayMs,
            'deadlock_retries' => $this->allowDeadlockRetries,
            'foreign_keys'     => $this->checkForeignKeys,
            'two_phase'        => $this->twoPhaseCommit->value,
            'max_affected'     => $this->maxAffectedRows,
        ];
    }
}