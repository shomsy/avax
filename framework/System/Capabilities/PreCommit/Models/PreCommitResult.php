<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Models;

/**
 * PreCommit Result
 *
 * Aggregates all check results and determines commit status.
 */
final class PreCommitResult
{
    public const STATUS_PASSED  = 'passed';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_WARNING = 'warning';
    public const STATUS_INFO    = 'info';

    private string $status;
    private string $timestamp;
    private array  $issues;
    private array  $passedChecks;
    private float  $executionTime;
    private array  $metadata;

    public function __construct()
    {
        $this->status        = self::STATUS_PASSED;
        $this->timestamp     = date('c');
        $this->issues        = [];
        $this->passedChecks  = [];
        $this->executionTime = 0.0;
        $this->metadata      = [];
    }

    public function addIssue(PreCommitIssue $issue) : void
    {
        $this->issues[] = $issue;
    }

    public function addPassedCheck(string $checkName) : void
    {
        $this->passedChecks[] = $checkName;
    }

    public function addMetadata(string $key, mixed $value) : void
    {
        $this->metadata[$key] = $value;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    public function getTimestamp() : string
    {
        return $this->timestamp;
    }

    /** @return array<PreCommitIssue> */
    public function getIssues() : array
    {
        return $this->issues;
    }

    /** @return array<string> */
    public function getPassedChecks() : array
    {
        return $this->passedChecks;
    }

    public function getExecutionTime() : float
    {
        return $this->executionTime;
    }

    public function setExecutionTime(float $seconds) : void
    {
        $this->executionTime = $seconds;
    }

    public function getMetadata() : array
    {
        return $this->metadata;
    }

    public function isBlocked() : bool
    {
        $this->status = $this->determineStatus();

        return $this->status === self::STATUS_BLOCKED;
    }

    public function determineStatus() : string
    {
        if (! empty($this->getCriticalIssues()) || ! empty($this->getErrorIssues())) {
            return self::STATUS_BLOCKED;
        }

        if (! empty($this->getWarningIssues())) {
            return self::STATUS_WARNING;
        }

        if (! empty($this->getInfoIssues())) {
            return self::STATUS_INFO;
        }

        return self::STATUS_PASSED;
    }

    public function getCriticalIssues() : array
    {
        return array_filter(
            $this->issues,
            fn ($issue) => $issue->getSeverity() === PreCommitIssue::SEVERITY_CRITICAL
        );
    }

    public function getErrorIssues() : array
    {
        return array_filter(
            $this->issues,
            fn ($issue) => $issue->getSeverity() === PreCommitIssue::SEVERITY_ERROR
        );
    }

    public function getWarningIssues() : array
    {
        return array_filter(
            $this->issues,
            fn ($issue) => $issue->getSeverity() === PreCommitIssue::SEVERITY_WARNING
        );
    }

    public function getInfoIssues() : array
    {
        return array_filter(
            $this->issues,
            fn ($issue) => $issue->getSeverity() === PreCommitIssue::SEVERITY_INFO
        );
    }

    public function isPassed() : bool
    {
        $this->status = $this->determineStatus();

        return $this->status === self::STATUS_PASSED;
    }

    public function toArray() : array
    {
        return [
            'status'                 => $this->determineStatus(),
            'timestamp'              => $this->timestamp,
            'execution_time_seconds' => $this->executionTime,
            'summary'                => [
                'total'                    => $this->getTotalIssueCount(),
                'critical'                 => $this->getCriticalCount(),
                'errors'                   => $this->getErrorCount(),
                'warnings'                 => $this->getWarningCount(),
                'info'                     => $this->getInfoCount(),
                'passed_checks'            => count($this->passedChecks),
                'delete_candidates'        => count($this->getDeleteCandidates()),
                'auto_fix_candidates'      => count($this->getAutoFixCandidates()),
                'manual_review_candidates' => count($this->getManualReviewCandidates()),
            ],
            'issues'                 => array_map(
                fn ($issue) => $issue->toArray(),
                $this->issues
            ),
            'passed_checks'          => $this->passedChecks,
            'metadata'               => $this->metadata,
        ];
    }

    public function getTotalIssueCount() : int
    {
        return count($this->issues);
    }

    public function getCriticalCount() : int
    {
        return count($this->getCriticalIssues());
    }

    public function getErrorCount() : int
    {
        return count($this->getErrorIssues());
    }

    public function getWarningCount() : int
    {
        return count($this->getWarningIssues());
    }

    public function getInfoCount() : int
    {
        return count($this->getInfoIssues());
    }

    public function getDeleteCandidates() : array
    {
        return array_filter(
            $this->issues,
            fn ($issue) => $issue->canDelete()
        );
    }

    public function getAutoFixCandidates() : array
    {
        return array_filter(
            $this->issues,
            fn ($issue) => $issue->canAutoFix()
        );
    }

    public function getManualReviewCandidates() : array
    {
        return array_filter(
            $this->issues,
            fn ($issue) => $issue->requiresReview()
        );
    }

    public function getSummaryText() : string
    {
        $status = $this->determineStatus();

        $statusIcon = match ($status) {
            self::STATUS_PASSED  => '✅',
            self::STATUS_WARNING => '⚠️',
            self::STATUS_BLOCKED => '🚫',
            default              => 'ℹ️',
        };

        $output = "\n";
        $output .= "═══════════════════════════════════════════════════════════════\n";
        $output .= "  PRE-COMMIT DISCIPLINE REPORT\n";
        $output .= "═══════════════════════════════════════════════════════════════════════\n";
        $output .= "  Status   : {$statusIcon} " . strtoupper($status) . "\n";
        $output .= "  Timestamp: {$this->timestamp}\n";
        $output .= "  Duration : " . number_format($this->executionTime, 4) . "s\n";
        $output .= "───────────────────────────────────────────────────────────────\n";
        $output .= "  Summary :\n";
        $output .= "    Critical: " . $this->getCriticalCount() . "\n";
        $output .= "    Errors : " . $this->getErrorCount() . "\n";
        $output .= "    Warnings: " . $this->getWarningCount() . "\n";
        $output .= "    Info   : " . $this->getInfoCount() . "\n";
        $output .= "    Passed : " . count($this->passedChecks) . "\n";
        $output .= "───────────────────────────────────────────────────────────────\n";

        if (! empty($this->getCriticalIssues())) {
            $output .= "  CRITICAL ISSUES (Block Commit):\n";
            foreach ($this->getCriticalIssues() as $issue) {
                $output .= "    🚫 " . $issue->getMessage();
                if ($issue->getFile()) {
                    $output .= " (" . $issue->getLocation() . ")";
                }
                $output .= "\n";
            }
            $output .= "\n";
        }

        if (! empty($this->getErrorIssues())) {
            $output .= "  ERRORS (Block Commit):\n";
            foreach ($this->getErrorIssues() as $issue) {
                $output .= "    ❌ " . $issue->getMessage();
                if ($issue->getFile()) {
                    $output .= " (" . $issue->getLocation() . ")";
                }
                $output .= "\n";
            }
            $output .= "\n";
        }

        if (! empty($this->getWarningIssues())) {
            $output .= "  WARNINGS (Allow with Warning):\n";
            foreach ($this->getWarningIssues() as $issue) {
                $output .= "    ⚠️ " . $issue->getMessage();
                if ($issue->getFile()) {
                    $output .= " (" . $issue->getLocation() . ")";
                }
                $output .= "\n";
            }
            $output .= "\n";
        }

        if (! empty($this->getInfoIssues())) {
            $output .= "  INFO:\n";
            foreach ($this->getInfoIssues() as $issue) {
                $output .= "    ℹ️ " . $issue->getMessage() . "\n";
            }
            $output .= "\n";
        }

        if (! empty($this->getDeleteCandidates())) {
            $output .= "  DELETE CANDIDATES (Requires Review):\n";
            foreach ($this->getDeleteCandidates() as $issue) {
                $output .= "    🗑️ " . $issue->getMessage();
                $output .= " [" . $issue->getDeleteClassification() . "]";
                $output .= "\n";
            }
            $output .= "\n";
        }

        $output .= "═══════════════════════════════════════════════════════════════\n";
        if ($this->canCommit()) {
            $output .= "  Commit can proceed.\n";
            if (! empty($this->getWarningIssues())) {
                $output .= "  Note: " . count($this->getWarningIssues()) . " warnings found.\n";
            }
        } else {
            $output .= "  🚫 COMMIT BLOCKED - Fix critical issues first.\n";
        }
        $output .= "═══════════════════════════════════════════════════════════════\n";

        return $output;
    }

    public function canCommit() : bool
    {
        $this->status = $this->determineStatus();

        return in_array($this->status, [self::STATUS_PASSED, self::STATUS_WARNING, self::STATUS_INFO], true);
    }

    public function getTodoLines() : array
    {
        $lines = [];

        foreach ($this->issues as $issue) {
            $lines[] = $issue->toTodoLine();
        }

        return $lines;
    }
}
