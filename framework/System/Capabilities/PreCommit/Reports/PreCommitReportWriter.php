<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Reports;

use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitResult;

/**
 * PreCommit Report Writer
 *
 * Writes validation reports to files.
 * Saves reports to Code-Review-And-ToDo/pre-commit/
 */
final class PreCommitReportWriter
{
    private string $reportPath;
    private string $todoPath;

    public function __construct(?string $reportPath = null, ?string $todoPath = null)
    {
        $basePath = getcwd();

        $this->reportPath = $reportPath ?? $basePath . '/Code-Review-And-ToDo/pre-commit';
        $this->todoPath   = $todoPath ?? $basePath . '/Code-Review-And-ToDo/pre-commit/pre-commit-todo.md';
    }

    public function writeReport(PreCommitResult $result) : bool
    {
        $timestamp = date('Y-m-d_His');
        $filename  = "pre-commit-report-{$timestamp}.json";
        $filepath  = $this->reportPath . '/' . $filename;

        $data = $result->toArray();
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            return false;
        }

        if (! is_dir($this->reportPath) && ! mkdir($this->reportPath, 0755, true)) {
            return false;
        }

        if (file_put_contents($filepath, $json) === false) {
            return false;
        }

        // Write latest symlink
        $latestPath = $this->reportPath . '/latest.json';
        @copy($filepath, $latestPath);

        // Write markdown report
        $this->writeMarkdownReport($result);

        return true;
    }

    public function writeMarkdownReport(PreCommitResult $result) : bool
    {
        $timestamp = date('Y-m-d_His');
        $filename  = "pre-commit-report-{$timestamp}.md";
        $filepath  = $this->reportPath . '/' . $filename;

        $content = $this->generateMarkdown($result);

        if (! is_dir($this->reportPath) && ! mkdir($this->reportPath, 0755, true)) {
            return false;
        }

        return file_put_contents($filepath, $content) !== false;
    }

    private function generateMarkdown(PreCommitResult $result) : string
    {
        $status = $result->determineStatus();

        $content = "# Pre-Commit Discipline Report\n\n";
        $content .= "**Status:** " . strtoupper($status) . "\n";
        $content .= "**Timestamp:** " . $result->getTimestamp() . "\n";
        $content .= "**Duration:** " . number_format($result->getExecutionTime(), 4) . "s\n\n";

        $content .= "## Summary\n\n";
        $content .= "| Severity | Count |\n";
        $content .= "|----------|-------|\n";
        $content .= "| Critical | " . $result->getCriticalCount() . " |\n";
        $content .= "| Errors | " . $result->getErrorCount() . " |\n";
        $content .= "| Warnings | " . $result->getWarningCount() . " |\n";
        $content .= "| Info | " . $result->getInfoCount() . " |\n";
        $content .= "| Passed Checks | " . count($result->getPassedChecks()) . " |\n\n";

        $critical = $result->getCriticalIssues();
        if (! empty($critical)) {
            $content .= "## Critical Issues (Block Commit)\n\n";
            foreach ($critical as $issue) {
                $content .= "- **" . $issue->getCheckName() . "**: " . $issue->getMessage();
                if ($issue->getFile()) {
                    $content .= " (`" . $issue->getLocation() . "`";
                }
                $content .= ")\n";
            }
            $content .= "\n";
        }

        $errors = $result->getErrorIssues();
        if (! empty($errors)) {
            $content .= "## Errors (Block Commit)\n\n";
            foreach ($errors as $issue) {
                $content .= "- **" . $issue->getCheckName() . "**: " . $issue->getMessage();
                if ($issue->getFile()) {
                    $content .= " (`" . $issue->getLocation() . "`)";
                }
                $content .= "\n";
            }
            $content .= "\n";
        }

        $warnings = $result->getWarningIssues();
        if (! empty($warnings)) {
            $content .= "## Warnings (Allow with Warning)\n\n";
            foreach ($warnings as $issue) {
                $content .= "- **" . $issue->getCheckName() . "**: " . $issue->getMessage();
                if ($issue->getFile()) {
                    $content .= " (`" . $issue->getLocation() . "`)";
                }
                $content .= "\n";
            }
            $content .= "\n";
        }

        $deleteCandidates = $result->getDeleteCandidates();
        if (! empty($deleteCandidates)) {
            $content .= "## Delete Candidates (Requires Review)\n\n";
            $content .= "| File | Classification | Reason |\n";
            $content .= "|------|--------------|-------|\n";
            foreach ($deleteCandidates as $issue) {
                $content .= "| " . ($issue->getFile() ?? 'unknown');
                $content .= " | " . $issue->getDeleteClassification();
                $content .= " | " . $issue->getMessage() . " |\n";
            }
            $content .= "\n";
        }

        $autoFixCandidates = $result->getAutoFixCandidates();
        if (! empty($autoFixCandidates)) {
            $content .= "## Auto-Fix Candidates\n\n";
            foreach ($autoFixCandidates as $issue) {
                $content .= "- **" . $issue->getCheckName() . "**: " . $issue->getMessage() . "\n";
            }
            $content .= "\n";
        }

        $passedChecks = $result->getPassedChecks();
        if (! empty($passedChecks)) {
            $content .= "## Passed Checks\n\n";
            foreach ($passedChecks as $check) {
                $content .= "- ✅ " . $check . "\n";
            }
            $content .= "\n";
        }

        $content .= "---\n";
        if ($result->canCommit()) {
            $content .= "\n✅ **Commit can proceed.**\n";
        } else {
            $content .= "\n🚫 **COMMIT BLOCKED - Fix critical issues first.**\n";
        }

        return $content;
    }

    public function writeTodo(PreCommitResult $result) : bool
    {
        $lines = $result->getTodoLines();

        if (empty($lines)) {
            return true;
        }

        if (! is_dir($this->reportPath) && ! mkdir($this->reportPath, 0755, true)) {
            return false;
        }

        $header = "# Pre-Commit TODO\n\n";
        $header .= "Generated: " . $result->getTimestamp() . "\n\n";
        $header .= "## Summary\n";
        $header .= "- Critical: " . $result->getCriticalCount() . "\n";
        $header .= "- Errors: " . $result->getErrorCount() . "\n";
        $header .= "- Warnings: " . $result->getWarningCount() . "\n";
        $header .= "- Auto-fix candidates: " . count($result->getAutoFixCandidates()) . "\n";
        $header .= "- Delete candidates: " . count($result->getDeleteCandidates()) . "\n\n";
        $header .= "## Tasks\n\n";

        $content = $header . implode("\n", $lines) . "\n";

        return file_put_contents($this->todoPath, $content) !== false;
    }

    public function getLatest() : ?array
    {
        $latestPath = $this->reportPath . '/latest.json';
        if (! file_exists($latestPath)) {
            return null;
        }

        $data = file_get_contents($latestPath);

        return $data === false ? null : json_decode($data, true);
    }

    /** @return array<string> */
    public function listReports() : array
    {
        if (! is_dir($this->reportPath)) {
            return [];
        }

        $files = glob($this->reportPath . '/pre-commit-report-*.json');
        if ($files === false) {
            return [];
        }

        usort($files, fn ($a, $b) => filemtime($b) - filemtime($a));

        return $files;
    }

    public function getReportPath() : string
    {
        return $this->reportPath;
    }

    public function getTodoPath() : string
    {
        return $this->todoPath;
    }
}
