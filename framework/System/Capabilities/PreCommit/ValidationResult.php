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
    private bool $passed;
    /** @var array<string> */
    private array $messages;
    private ?string $severity;
    private ?string $file;
    private ?int $line;
    private ?string $ruleCode;

    public function __construct(
        bool $passed,
        array $messages = [],
        ?string $severity = 'info',
        ?string $file = null,
        ?int $line = null,
        ?string $ruleCode = null
    ) {
        $this->passed = $passed;
        $this->messages = $messages;
        $this->severity = $severity;
        $this->file = $file;
        $this->line = $line;
        $this->ruleCode = $ruleCode;
    }

    public function isPassed(): bool
    {
        return $this->passed;
    }

    public function isFailed(): bool
    {
        return !$this->passed;
    }

    /** @return array<string> */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getSeverity(): ?string
    {
        return $this->severity;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    public function getRuleCode(): ?string
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
        $combined = new self(
            $this->passed && $other->passed,
            array_merge($this->messages, $other->getMessages()),
            $this->getWorstSeverity($other),
            null,
            null,
            null
        );
        return $combined;
    }

    private function getWorstSeverity(self $other): string
    {
        $severityLevels = ['info' => 0, 'warning' => 1, 'error' => 2, 'critical' => 3];
        $current = $severityLevels[$this->severity] ?? 0;
        $otherLevel = $severityLevels[$other->severity] ?? 0;
        $worstCode = $current >= $otherLevel ? $this->severity : $other->severity;
        return $worstCode ?? 'info';
    }

    /**
     * Factory methods for common outcomes
     */
    public static function pass(string $message = ''): self
    {
        return new self(true, $message ? [$message] : [], 'info');
    }

    public static function fail(string $message, ?string $severity = 'error', ?string $file = null, ?int $line = null, ?string $ruleCode = null): self
    {
        return new self(false, [$message], $severity, $file, $line, $ruleCode);
    }
}
