<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation;

/**
 * CompiledMethodPolicy — Compiled per-method failure policy with schema version and checksum.
 */
final readonly class CompiledMethodPolicy
{
    public function __construct(
        public string $targetClass,
        public string $targetMethod,
        public FailurePolicy $policy,
        public int $schemaVersion = 1,
        public string $checksum = '',
        public int $sourceMtime = 0,
        public int $compiledAt = 0,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'schema_version' => $this->schemaVersion,
            'target_class' => $this->targetClass,
            'target_method' => $this->targetMethod,
            'checksum' => $this->checksum,
            'source_mtime' => $this->sourceMtime,
            'compiled_at' => $this->compiledAt,
            'policy' => $this->policyToArray(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function policyToArray(): array
    {
        return [
            'actions' => array_map(fn(FailureAction $a) => [
                'exception_class' => $a->exceptionClass,
                'decision' => $a->decision->value,
                'status_code' => $a->statusCode,
                'message_key' => $a->messageKey,
                'report_channel' => $a->reportChannel,
                'fallback_class' => $a->fallbackClass,
                'dead_letter_queue' => $a->deadLetterQueue,
            ], $this->policy->actions),
            'retry_max_attempts' => $this->policy->retryMaxAttempts,
            'retry_backoff' => $this->policy->retryBackoff,
            'retry_delay_ms' => $this->policy->retryDelayMs,
            'retry_jitter' => $this->policy->retryJitter,
            'fallback_class' => $this->policy->fallbackClass,
            'report_channel' => $this->policy->reportChannel,
            'dead_letter_queue' => $this->policy->deadLetterQueue,
            'timeout_ms' => $this->policy->timeoutMs,
            'recover_with_class' => $this->policy->recoverWithClass,
            'rethrow_except' => $this->policy->rethrowExcept,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $policy = new FailurePolicy(
            actions: array_map(fn(array $a) => new FailureAction(
                exceptionClass: $a['exception_class'],
                decision: FailureDecision::from($a['decision']),
                statusCode: $a['status_code'] ?? null,
                messageKey: $a['message_key'] ?? null,
                reportChannel: $a['report_channel'] ?? null,
                fallbackClass: $a['fallback_class'] ?? null,
                deadLetterQueue: $a['dead_letter_queue'] ?? null,
                meta: $a['meta'] ?? [],
            ), $data['policy']['actions'] ?? []),
            retryMaxAttempts: $data['policy']['retry_max_attempts'] ?? null,
            retryBackoff: $data['policy']['retry_backoff'] ?? 'none',
            retryDelayMs: $data['policy']['retry_delay_ms'] ?? 0,
            retryJitter: $data['policy']['retry_jitter'] ?? false,
            fallbackClass: $data['policy']['fallback_class'] ?? null,
            reportChannel: $data['policy']['report_channel'] ?? null,
            deadLetterQueue: $data['policy']['dead_letter_queue'] ?? null,
            timeoutMs: $data['policy']['timeout_ms'] ?? null,
            recoverWithClass: $data['policy']['recover_with_class'] ?? null,
            rethrowExcept: $data['policy']['rethrow_except'] ?? [],
        );

        return new self(
            targetClass: $data['target_class'] ?? '',
            targetMethod: $data['target_method'] ?? '',
            policy: $policy,
            schemaVersion: $data['schema_version'] ?? 1,
            checksum: $data['checksum'] ?? '',
            sourceMtime: $data['source_mtime'] ?? 0,
            compiledAt: $data['compiled_at'] ?? 0,
        );
    }

    public function isStale(): bool
    {
        if ($this->sourceMtime === 0) {
            return false;
        }
        if (!class_exists($this->targetClass)) {
            return true;
        }
        $ref = new \ReflectionClass($this->targetClass);
        $file = $ref->getFileName();
        if ($file === false || !file_exists($file)) {
            return true;
        }
        return filemtime($file) > $this->sourceMtime;
    }
}
