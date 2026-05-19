<?php

declare(strict_types=1);

namespace Avax\Components\DataLayer\CommitDataChanges;

final class DataTransactionPolicy
{
    public function __construct(
        public int $maxAttempts = 3,
        public bool $retryTransientFailures = true,
        public bool $idempotent = false
    ) {
    }
}
