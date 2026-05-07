<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\Configuration;

final readonly class TasksConfiguration
{
    public function __construct(
        public int $defaultRetryAttempts = 3,
        public int $defaultRetryBackoffMs = 1000,
        public int $defaultTimeoutSeconds = 300,
        public int $maxConcurrentTasks = 10,
    ) {}
}
