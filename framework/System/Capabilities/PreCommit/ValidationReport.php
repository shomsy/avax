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
    private readonly string $timestamp;

    /** @var list<ValidationResult> */
    private array $results = [];

    private string $reportId;

    /** @var array<string, mixed> */
    private array $metadata = [];

    private float $executionTime = 0.0;

    public function __construct()
    {
        $this->timestamp = date('c');
        $this->reportId = uniqid('val-', true);
    }

    public function addResult(ValidationResult $validationResult): void
    {
        $this->results[] = $validationResult;
    }

    /**
     * @return list<ValidationResult>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /** @return list<ValidationResult> */
    public function getFailedResults(): array
    {
        return array_values(array_filter(
            $this->results,
            static fn (ValidationResult $validationResult): bool => $validationResult->isFailed()
        ));
    }

    /** @return list<ValidationResult> */
    public function getPassedResults(): array
    {
        return array_values(array_filter(
            $this->results,
            static fn (ValidationResult $validationResult): bool => $validationResult->isPassed()
        ));
    }

    public function isAllPassed(): bool
    {
        return $this->getFailedResults() === [];
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

    /** @return array<string, mixed> */
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
            'results' => array_map(fn (ValidationResult $validationResult): array => [
                'passed' => $validationResult->isPassed(),
                'severity' => $validationResult->getSeverity(),
                'messages' => $validationResult->getMessages(),
                'file' => $validationResult->getFile(),
                'line' => $validationResult->getLine(),
                'rule_code' => $validationResult->getRuleCode(),
            ], $this->results),
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
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
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
        $output .= sprintf('  Report ID : %s%s', $this->reportId, PHP_EOL);
        $output .= sprintf('  Timestamp : %s%s', $this->timestamp, PHP_EOL);
        $output .= '  Duration  : '.number_format($this->executionTime, 4)."s\n";
        $output .= "───────────────────────────────────────────────────────────────\n";
        $output .= "  Results   : {$this->getPassedCount()}/{$this->getTotalCount()} passed\n";
        if ($this->getFailedCount() > 0) {
            $output .= "  Failed    : {$this->getFailedCount()} ❌\n";
        }

        $output .= "───────────────────────────────────────────────────────────────\n";

        foreach ($this->results as $result) {
            $status = $result->isPassed() ? '✅' : '❌';
            $severity = strtoupper($result->getSeverity());
            $output .= sprintf("  %s [%s] %s\n", $status, $severity, implode('; ', $result->getMessages()));
        }

        $output .= "═══════════════════════════════════════════════════════════════\n";
        if ($this->isAllPassed()) {
            $output .= "  ✅ ALL VALIDATIONS PASSED\n";
        } else {
            $output .= "  ❌ VALIDATION FAILED\n";
        }

        return $output."═══════════════════════════════════════════════════════════════\n\n";
    }
}
