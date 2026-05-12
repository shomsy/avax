<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation;

use Throwable;

/**
 * FailurePolicy — Compiled failure policy for a target method.
 */
final readonly class FailurePolicy
{
    /**
     * @param FailureAction[] $actions
     */
    public function __construct(
        public array $actions = [],
        public int|null $retryMaxAttempts = null,
        public string $retryBackoff = 'none',
        public int $retryDelayMs = 0,
        public bool $retryJitter = false,
        public string|null $fallbackClass = null,
        public string|null $reportChannel = null,
        public string|null $deadLetterQueue = null,
        public int|null $timeoutMs = null,
        public string|null $recoverWithClass = null,
        /** @var class-string<Throwable>[] */
        public array $rethrowExcept = [],
    ) {
    }

    public function hasRetry(): bool
    {
        return $this->retryMaxAttempts !== null && $this->retryMaxAttempts > 0;
    }

    public function hasFallback(): bool
    {
        return $this->fallbackClass !== null;
    }

    public function hasDeadLetter(): bool
    {
        return $this->deadLetterQueue !== null;
    }

    public function hasRecovery() : bool
    {
        return $this->recoverWithClass !== null;
    }

    /**
     * @param class-string<Throwable> $exceptionClass
     */
    public function findAction(string $exceptionClass): ?FailureAction
    {
        foreach ($this->actions as $action) {
            if ($exceptionClass === $action->exceptionClass || is_subclass_of($exceptionClass, $action->exceptionClass)) {
                return $action;
            }
        }
        return null;
    }
}
