<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Transactions;

/**
 * Report containing deadlock detection analysis.
 *
 * Readonly value object representing a detected deadlock situation.
 */
final readonly class DeadlockReport
{
    public function __construct(
        public bool   $isDeadlock,
        public string $type = '',
        public string $message = '',
        public string $errorCode = '',
        public string $suggestion = '',
        public array  $affectedTables = [],
        public float  $detectedAt = 0.0,
    )
    {
    }

    /**
     * Creates a report indicating a detected deadlock.
     */
    public static function deadlock(
        string $type,
        string $message,
        string $errorCode,
        string $suggestion,
        array  $affectedTables = [],
    ): self
    {
        return new self(
            isDeadlock: true,
            type: $type,
            message: $message,
            errorCode: $errorCode,
            suggestion: $suggestion,
            affectedTables: $affectedTables,
            detectedAt: microtime(true),
        );
    }

    /**
     * Creates a report indicating no deadlock was detected.
     */
    public static function notDeadlock(): self
    {
        return new self(
            isDeadlock: false,
            type: 'none',
            message: 'No deadlock pattern detected',
            detectedAt: microtime(true),
        );
    }

    /**
     * Returns a human-readable summary of the deadlock report.
     */
    public function summary(): string
    {
        if (!$this->isDeadlock) {
            return 'No deadlock detected';
        }

        $summary = "Deadlock Detected ({$this->type})\n";
        $summary .= sprintf('Error Code: %s%s', $this->errorCode, PHP_EOL);
        $summary .= sprintf('Message: %s%s', $this->message, PHP_EOL);

        if ($this->affectedTables !== []) {
            $summary .= 'Affected Tables: ' . implode(', ', $this->affectedTables);
            $summary .= "\n";
        }

        return $summary . ('Suggestion: ' . $this->suggestion);
    }
}
