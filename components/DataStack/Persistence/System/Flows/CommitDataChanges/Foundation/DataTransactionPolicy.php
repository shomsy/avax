<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\CommitDataChanges\Foundation;

final class DataTransactionPolicy
{
    public function __construct(
        public int $maxAttempts = 3,
        public bool $retryTransientFailures = true,
        public bool $idempotent = false
    ) {
    }
}
