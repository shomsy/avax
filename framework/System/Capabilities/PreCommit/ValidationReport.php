<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit;

/**
 * Validation Report Storage
 * 
 * JSON-based report storage with timestamp tracking.
 * Stores validation history for audit trail.
 */
final class ValidationReport
{
    private string $timestamp;
    /** @var array<ValidationResult> */
    private array $results;
    private string $reportId;
    private array $metadata;
    private float $executionTime;

    public function __construct()
    {
        $this->timestamp = date('c');
        $this->results = [];
        $this->reportId = uniqid('val-', true);
        $this->metadata = [];
        $this->executionTime = 0.0;
    }

    public function addResult(ValidationResult $result): void
    {
        $this->results[] = $result;
    }

    /**
     * @return array<ValidationResult>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    public function getFailedResults(): array
    {
        return array_filter($this->results, fn($r) => $r->isFailed());
    }

    public function getPassedResults(): array
    {
        return array_filter($this->results, fn($r) => $r->isPassed());
    }

    public function isAllPassed(): bool
    {
        return count($this->getFailedResults()) === 0;
    }

    public function getTotalCount(): int
    {
        return count($this->results);
    }

    public function getFailedCount(): int
    {
        return count($this->getFailedResults());
    }

    public function getPassedCount(): int
    {
        return count($this->getPassedResults());
    }

    public function setReportId(string $id): void
    {
        $this->reportId = $id;
    }

    public function getReportId(): string
    {
        return $this->reportId;
    }

    public function setExecutionTime(float $seconds): void
    {
        $this->executionTime = $seconds;
    }

    public function getExecutionTime(): float
    {
        return $this->executionTime;
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * Convert report to JSON-serializable array
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'report_id' => $this->reportId,
            'timestamp' => $this->timestamp,
            'execution_time_seconds' => $this->executionTime,
            'summary' => [
                'total' => $this->getTotalCount(),
                'passed' => $this->getPassedCount(),
                'failed' => $this->getFailedCount(),
                'all_passed' => $this->isAllPassed(),
            ],
            'metadata' => $this->metadata,
            'results' => array_map(function (ValidationResult $r) {
                return [
                    'passed' => $r->isPassed(),
                    'severity' => $r->getSeverity(),
                    'messages' => $r->getMessages(),
                    'file' => $r->getFile(),
                    'line' => $r->getLine(),
                    'rule_code' => $r->getRuleCode(),
                ];
            }, $this->results),
        ];
    }

    /**
     * Save report to JSON file
     */
    public function saveToFile(string $outputPath): bool
    {
        $data = json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($data === false) {
            return false;
        }
        $dir = dirname($outputPath);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }
        return file_put_contents($outputPath, $data) !== false;
    }

    /**
     * Generate human-readable summary text
     */
    public function getSummaryText(): string
    {
        $output = "\n";
        $output .= "═══════════════════════════════════════════════════════════════\n";
        $output .= "  PRE-COMMIT VALIDATION REPORT\n";
        $output .= "═══════════════════════════════════════════════════════════════\n";
        $output .= "  Report ID : {$this->reportId}\n";
        $output .= "  Timestamp : {$this->timestamp}\n";
        $output .= "  Duration  : " . number_format($this->executionTime, 4) . "s\n";
        $output .= "───────────────────────────────────────────────────────────────\n";
        $output .= "  Results   : {$this->getPassedCount()}/{$this->getTotalCount()} passed\n";
        if ($this->getFailedCount() > 0) {
            $output .= "  Failed    : {$this->getFailedCount()} ❌\n";
        }
        $output .= "───────────────────────────────────────────────────────────────\n";

        foreach ($this->results as $idx => $result) {
            $status = $result->isPassed() ? '✅' : '❌';
            $severity = strtoupper($result->getSeverity() ?? 'INFO');
            $output .= sprintf("  %s [%s] %s\n", $status, $severity, implode('; ', $result->getMessages()));
        }

        $output .= "═══════════════════════════════════════════════════════════════\n";
        if ($this->isAllPassed()) {
            $output .= "  ✅ ALL VALIDATIONS PASSED\n";
        } else {
            $output .= "  ❌ VALIDATION FAILED\n";
        }
        $output .= "═══════════════════════════════════════════════════════════════\n\n";

        return $output;
    }
}
