<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Todo;

/**
 * Todo Generator
 * 
 * Creates todo tasks from validation failures.
 */
final readonly class TodoGenerator
{
    private string $todoFile;

    public function __construct(?string $todoFile = null)
    {
        $this->todoFile = $todoFile ?? (getcwd() . '/.agents/management/TODO.md');
    }

    /**
     * Generate todo tasks from validation failures
     *
     * @param list<array{messages: list<string>|string, severity: string, file: string|null, line: int|null, rule_code: string|null}> $failures
     */
    public function generate(array $failures, string $reportId): void
    {
        if ($failures === []) {
            return;
        }

        $tasks = $this->createTasks($failures, $reportId);
        $this->appendTasks($tasks);
    }

    /**
     * Create todo task entries
     *
     * @param list<array{messages: list<string>|string, severity: string, file: string|null, line: int|null, rule_code: string|null}> $failures
     *
     * @return list<string>
     */
    private function createTasks(array $failures, string $reportId): array
    {
        $tasks = [];
        $date = date('Y-m-d');

        foreach ($failures as $failure) {
            $file = $failure['file'] ?? 'general';
            $line = $failure['line'] ?? '';
            $location = $file . ($line ? ':' . $line : '');
            $messages = is_array($failure['messages']) ? $failure['messages'] : [$failure['messages']];
            $severity = $failure['severity'];
            $ruleCode = $failure['rule_code'] ?? 'UNKNOWN';

            $priority = $this->mapSeverityToPriority($severity);

            foreach ($messages as $message) {
                $tasks[] = sprintf('- [ ] [%s] [%s] [%s] Fix: %s - Location: %s - Ref: %s', $date, $priority, $ruleCode, $message, $location, $reportId);
            }
        }

        return $tasks;
    }

    /**
     * Append tasks to TODO.md
     * 
     * @param array<string> $tasks
     */
    private function appendTasks(array $tasks): void
    {
        if ($tasks === []) {
            return;
        }

        // Ensure directory exists
        $dir = dirname($this->todoFile);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return;
        }

        $header = "";
        if (!file_exists($this->todoFile)) {
            $header = "# Todo Tasks\n\nGenerated from validation failures.\n\n";
        }

        $content = $header . implode("\n", $tasks) . "\n\n";

        file_put_contents($this->todoFile, $content, FILE_APPEND);
    }

    /**
     * Map severity to priority level
     */
    private function mapSeverityToPriority(string $severity): string
    {
        $map = [
            'critical' => 'HIGH',
            'error' => 'HIGH',
            'warning' => 'MEDIUM',
            'info' => 'LOW',
        ];

        return $map[$severity] ?? 'MEDIUM';
    }
}
