<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Configuration;

/**
 * FailureBoundaryConfiguration — Configuration DTO for the failure boundary.
 */
final readonly class FailureBoundaryConfiguration
{
    public function __construct(
        public string $defaultReportChannel = 'default',
        public string $defaultDeadLetterQueue = 'failed',
        public int $defaultRetryMaxAttempts = 3,
        public string $defaultRetryBackoff = 'none',
        public int $defaultRetryDelayMs = 0,
        public string $artifactDir = '',
        public bool $enableArtifactPersistence = false,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            defaultReportChannel: $config['default_report_channel'] ?? 'default',
            defaultDeadLetterQueue: $config['default_dead_letter_queue'] ?? 'failed',
            defaultRetryMaxAttempts: $config['default_retry_max_attempts'] ?? 3,
            defaultRetryBackoff: $config['default_retry_backoff'] ?? 'none',
            defaultRetryDelayMs: $config['default_retry_delay_ms'] ?? 0,
            artifactDir: $config['artifact_dir'] ?? '',
            enableArtifactPersistence: $config['enable_artifact_persistence'] ?? false,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'default_report_channel' => $this->defaultReportChannel,
            'default_dead_letter_queue' => $this->defaultDeadLetterQueue,
            'default_retry_max_attempts' => $this->defaultRetryMaxAttempts,
            'default_retry_backoff' => $this->defaultRetryBackoff,
            'default_retry_delay_ms' => $this->defaultRetryDelayMs,
            'artifact_dir' => $this->artifactDir,
            'enable_artifact_persistence' => $this->enableArtifactPersistence,
        ];
    }
}
