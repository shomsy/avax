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
final readonly class ReportStorage
{
    private string $reportDir;

    private string $todoFile;

    public function __construct(string|null $reportDir = null, string|null $todoFile = null)
    {
        $basePath = getcwd() ?: '.';
        $this->reportDir = $reportDir ?? $basePath.'/.agents/reports/validation';
        $this->todoFile = $todoFile ?? $basePath.'/.agents/management/TODO.md';
    }

    public function save(ValidationReport $validationReport, bool $saveTodo = true): bool
    {
        $timestamp = date('Y-m-d_His');
        $filename = sprintf('validation-%s-%s.json', $timestamp, $validationReport->getReportId());
        $filepath = $this->reportDir.'/'.$filename;

        if (! $validationReport->saveToFile($filepath)) {
            return false;
        }

        $latestPath = $this->reportDir.'/latest.json';
        @copy($filepath, $latestPath);

        if ($saveTodo && $validationReport->getFailedCount() > 0) {
            $this->generateTodo($validationReport);
        }

        return true;
    }

    private function generateTodo(ValidationReport $validationReport): void
    {
        $failures = [];
        foreach ($validationReport->getFailedResults() as $validationResult) {
            $failures[] = [
                'messages' => $validationResult->getMessages(),
                'severity' => $validationResult->getSeverity(),
                'file' => $validationResult->getFile(),
                'line' => $validationResult->getLine(),
                'rule_code' => $validationResult->getRuleCode(),
            ];
        }

        $todoGenerator = new TodoGenerator($this->todoFile);
        $todoGenerator->generate($failures, $validationReport->getReportId());
    }

    /** @return array<string, mixed>|null */
    public function getLatest() : array|null
    {
        $latestPath = $this->reportDir.'/latest.json';
        if (! file_exists($latestPath)) {
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
        if (! is_dir($this->reportDir)) {
            return [];
        }

        $files = glob($this->reportDir.'/validation-*.json');
        if ($files === false) {
            return [];
        }

        usort($files, fn ($a, $b): int => filemtime($b) - filemtime($a));

        return $files;
    }
}
