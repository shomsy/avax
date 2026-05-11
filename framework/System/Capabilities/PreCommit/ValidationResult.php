<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit;

/**
 * Validation Result Value Object
 *
 * Immutable container for validation outcomes.
 * Part of the Chain of Responsibility pattern.
 */
final class ValidationResult
{
    private string $severity;

    /**
     * @param  list<string>  $messages
     */
    public function __construct(
        private bool $passed,
        private array       $messages = [], string|null $severity = 'info',
        private string|null $file = null,
        private int|null    $line = null,
        private string|null $ruleCode = null
    ) {
        $this->severity = $severity ?? 'info';
    }

    public function isPassed(): bool
    {
        return $this->passed;
    }

    public function isFailed(): bool
    {
        return ! $this->passed;
    }

    /** @return list<string> */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getFile() : string|null
    {
        return $this->file;
    }

    public function getLine() : int|null
    {
        return $this->line;
    }

    public function getRuleCode() : string|null
    {
        return $this->ruleCode;
    }

    public function addMessage(string $message): self
    {
        $new = clone $this;
        $new->messages[] = $message;

        return $new;
    }

    public function withSeverity(string $severity): self
    {
        return new self(
            $this->passed,
            $this->messages,
            $severity,
            $this->file,
            $this->line,
            $this->ruleCode
        );
    }

    /**
     * Combine two results - logical AND for passed status
     */
    public function combine(self $other): self
    {
        return new self(
            $this->passed && $other->passed,
            array_merge($this->messages, $other->getMessages()),
            $this->getWorstSeverity($other)
        );
    }

    private function getWorstSeverity(self $other): string
    {
        $severityLevels = ['info' => 0, 'warning' => 1, 'error' => 2, 'critical' => 3];
        $current = $severityLevels[$this->severity] ?? 0;
        $otherLevel = $severityLevels[$other->severity] ?? 0;

        return $current >= $otherLevel ? $this->severity : $other->severity;
    }

    /**
     * Factory methods for common outcomes
     */
    public static function pass(string $message = ''): self
    {
        return new self(true, $message !== '' && $message !== '0' ? [$message] : [], 'info');
    }

    public static function fail(string $message, string|null $severity = 'error', string|null $file = null, int|null $line = null, string|null $ruleCode = null) : self
    {
        return new self(false, [$message], $severity, $file, $line, $ruleCode);
    }
}
