<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Report;

use Avax\Framework\System\Capabilities\PreCommit\Todo\TodoGenerator;
use Avax\Framework\System\Capabilities\PreCommit\ValidationReport;

/**
 * Report Storage
 * 
 * Manages persistence of validation reports.
 */
final class ReportStorage
{
    private string $reportDir;
    private string $todoFile;

    public function __construct(?string $reportDir = null, ?string $todoFile = null)
    {
        $basePath = getcwd() ?: '.';
        $this->reportDir = $reportDir ?? $basePath . '/.agents/reports/validation';
        $this->todoFile = $todoFile ?? $basePath . '/.agents/management/TODO.md';
    }

    public function save(ValidationReport $report, bool $saveTodo = true): bool
    {
        $timestamp = date('Y-m-d_His');
        $filename = "validation-{$timestamp}-{$report->getReportId()}.json";
        $filepath = $this->reportDir . '/' . $filename;

        if (!$report->saveToFile($filepath)) {
            return false;
        }

        $latestPath = $this->reportDir . '/latest.json';
        @copy($filepath, $latestPath);

        if ($saveTodo && $report->getFailedCount() > 0) {
            $this->generateTodo($report);
        }

        return true;
    }

    private function generateTodo(ValidationReport $report): void
    {
        $failures = [];
        foreach ($report->getFailedResults() as $result) {
            $failures[] = [
                'messages' => $result->getMessages(),
                'severity' => $result->getSeverity(),
                'file' => $result->getFile(),
                'line' => $result->getLine(),
                'rule_code' => $result->getRuleCode(),
            ];
        }

        $generator = new TodoGenerator($this->todoFile);
        $generator->generate($failures, $report->getReportId());
    }

    /** @return array<string, mixed>|null */
    public function getLatest(): ?array
    {
        $latestPath = $this->reportDir . '/latest.json';
        if (!file_exists($latestPath)) {
            return null;
        }

        $data = file_get_contents($latestPath);
        return $data === false ? null : json_decode($data, true);
    }

    /**
     * @return array<string>
     */
    public function list(): array
    {
        if (!is_dir($this->reportDir)) {
            return [];
        }

        $files = glob($this->reportDir . '/validation-*.json');
        if ($files === false) {
            return [];
        }

        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        return $files;
    }
}
