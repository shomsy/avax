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
final readonly class PreCommitReportWriter
{
    private string $reportPath;

    private string $todoPath;

    public function __construct(?string $reportPath = null, ?string $todoPath = null)
    {
        $basePath = getcwd();

        $this->reportPath = $reportPath ?? $basePath . '/Code-Review-And-ToDo/pre-commit';
        $this->todoPath   = $todoPath ?? $basePath . '/Code-Review-And-ToDo/pre-commit/pre-commit-todo.md';
    }

    public function writeReport(PreCommitResult $preCommitResult) : bool
    {
        $timestamp = date('Y-m-d_His');
        $filename = sprintf('pre-commit-report-%s.json', $timestamp);
        $filepath  = $this->reportPath . '/' . $filename;

        $data = $preCommitResult->toArray();
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
        $this->writeMarkdownReport($preCommitResult);

        return true;
    }

    public function writeMarkdownReport(PreCommitResult $preCommitResult) : bool
    {
        $timestamp = date('Y-m-d_His');
        $filename = sprintf('pre-commit-report-%s.md', $timestamp);
        $filepath  = $this->reportPath . '/' . $filename;

        $content = $this->generateMarkdown($preCommitResult);

        if (! is_dir($this->reportPath) && ! mkdir($this->reportPath, 0755, true)) {
            return false;
        }

        return file_put_contents($filepath, $content) !== false;
    }

    private function generateMarkdown(PreCommitResult $preCommitResult) : string
    {
        $status  = $preCommitResult->determineStatus();

        $content = "# Pre-Commit Discipline Report\n\n";
        $content .= "**Status:** " . strtoupper($status) . "\n";
        $content .= "**Timestamp:** " . $preCommitResult->getTimestamp() . "\n";
        $content .= "**Duration:** " . number_format($preCommitResult->getExecutionTime(), 4) . "s\n\n";

        $content .= "## Summary\n\n";
        $content .= "| Severity | Count |\n";
        $content .= "|----------|-------|\n";
        $content .= "| Critical | " . $preCommitResult->getCriticalCount() . " |\n";
        $content .= "| Errors | " . $preCommitResult->getErrorCount() . " |\n";
        $content .= "| Warnings | " . $preCommitResult->getWarningCount() . " |\n";
        $content .= "| Info | " . $preCommitResult->getInfoCount() . " |\n";
        $content .= "| Passed Checks | " . count($preCommitResult->getPassedChecks()) . " |\n\n";

        $critical = $preCommitResult->getCriticalIssues();
        if ($critical !== []) {
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

        $errors = $preCommitResult->getErrorIssues();
        if ($errors !== []) {
            $content .= "## Errors (Block Commit)\n\n";
            foreach ($errors as $error) {
                $content .= "- **" . $error->getCheckName() . "**: " . $error->getMessage();
                if ($error->getFile()) {
                    $content .= " (`" . $error->getLocation() . "`)";
                }

                $content .= "\n";
            }

            $content .= "\n";
        }

        $warnings = $preCommitResult->getWarningIssues();
        if ($warnings !== []) {
            $content .= "## Warnings (Allow with Warning)\n\n";
            foreach ($warnings as $warning) {
                $content .= "- **" . $warning->getCheckName() . "**: " . $warning->getMessage();
                if ($warning->getFile()) {
                    $content .= " (`" . $warning->getLocation() . "`)";
                }

                $content .= "\n";
            }

            $content .= "\n";
        }

        $deleteCandidates = $preCommitResult->getDeleteCandidates();
        if ($deleteCandidates !== []) {
            $content .= "## Delete Candidates (Requires Review)\n\n";
            $content .= "| File | Classification | Reason |\n";
            $content .= "|------|--------------|-------|\n";
            foreach ($deleteCandidates as $deleteCandidate) {
                $content .= "| " . ($deleteCandidate->getFile() ?? 'unknown');
                $content .= " | " . $deleteCandidate->getDeleteClassification();
                $content .= " | " . $deleteCandidate->getMessage() . " |\n";
            }

            $content .= "\n";
        }

        $autoFixCandidates = $preCommitResult->getAutoFixCandidates();
        if ($autoFixCandidates !== []) {
            $content .= "## Auto-Fix Candidates\n\n";
            foreach ($autoFixCandidates as $autoFixCandidate) {
                $content .= "- **" . $autoFixCandidate->getCheckName() . "**: " . $autoFixCandidate->getMessage() . "\n";
            }

            $content .= "\n";
        }

        $passedChecks = $preCommitResult->getPassedChecks();
        if ($passedChecks !== []) {
            $content .= "## Passed Checks\n\n";
            foreach ($passedChecks as $passedCheck) {
                $content .= "- ✅ " . $passedCheck . "\n";
            }

            $content .= "\n";
        }

        $content .= "---\n";
        if ($preCommitResult->canCommit()) {
            $content .= "\n✅ **Commit can proceed.**\n";
        } else {
            $content .= "\n🚫 **COMMIT BLOCKED - Fix critical issues first.**\n";
        }

        return $content;
    }

    public function writeTodo(PreCommitResult $preCommitResult) : bool
    {
        $lines  = $preCommitResult->getTodoLines();

        if ($lines === []) {
            return true;
        }

        if (! is_dir($this->reportPath) && ! mkdir($this->reportPath, 0755, true)) {
            return false;
        }

        $header = "# Pre-Commit TODO\n\n";
        $header .= "Generated: " . $preCommitResult->getTimestamp() . "\n\n";
        $header .= "## Summary\n";
        $header .= "- Critical: " . $preCommitResult->getCriticalCount() . "\n";
        $header .= "- Errors: " . $preCommitResult->getErrorCount() . "\n";
        $header .= "- Warnings: " . $preCommitResult->getWarningCount() . "\n";
        $header .= "- Auto-fix candidates: " . count($preCommitResult->getAutoFixCandidates()) . "\n";
        $header .= "- Delete candidates: " . count($preCommitResult->getDeleteCandidates()) . "\n\n";
        $header .= "## Tasks\n\n";

        $content = $header . implode("\n", $lines) . "\n";

        return file_put_contents($this->todoPath, $content) !== false;
    }

    /** @return array<string, mixed>|null */
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

        usort($files, fn ($a, $b) : int => filemtime($b) - filemtime($a));

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
