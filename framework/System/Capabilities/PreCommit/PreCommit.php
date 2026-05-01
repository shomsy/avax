<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit;

use Avax\Framework\System\Capabilities\PreCommit\Capabilities\CheckFileStructure;
use Avax\Framework\System\Capabilities\PreCommit\Capabilities\CheckForbiddenWords;
use Avax\Framework\System\Capabilities\PreCommit\Capabilities\CheckHowToRules;
use Avax\Framework\System\Capabilities\PreCommit\Capabilities\CheckNamingConventions;
use Avax\Framework\System\Capabilities\PreCommit\Capabilities\CheckPhpSyntax;
use Avax\Framework\System\Capabilities\PreCommit\Capabilities\CheckPublicSurfaceRules;
use Avax\Framework\System\Capabilities\PreCommit\Capabilities\DetectArchitectureViolations;
use Avax\Framework\System\Capabilities\PreCommit\Capabilities\DetectDeprecatedCode;
use Avax\Framework\System\Capabilities\PreCommit\Capabilities\DetectLegacyAliases;
use Avax\Framework\System\Capabilities\PreCommit\Capabilities\DetectLegacyCode;
use Avax\Framework\System\Capabilities\PreCommit\Capabilities\DetectTodoComments;
use Avax\Framework\System\Capabilities\PreCommit\Capabilities\DetectToolingScripts;
use Avax\Framework\System\Capabilities\PreCommit\Capabilities\RunExternalToolingScripts;
use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitResult;
use Avax\Framework\System\Capabilities\PreCommit\Reports\PreCommitReportWriter;
use Throwable;

/**
 * PreCommit - Main Entry Point
 *
 * Orchestrates all discipline checks in order:
 * 1. Detection first
 * 2. Classification second
 * 3. Safe processing third
 * 4. Report fourth
 * 5. Commit decision last
 */
final class PreCommit
{
    private PreCommitConfig       $config;
    private PreCommitResult       $result;
    private PreCommitReportWriter $reportWriter;
    /** @var list<string> */
    private array                 $files;
    private bool                  $touchedOnly;

    /**
     * @param list<string>|null $files
     */
    public function __construct(
        ?PreCommitConfig $config = null,
        ?array           $files = null,
        bool             $touchedOnly = true
    )
    {
        $this->config       = $config ?? new PreCommitConfig();
        $this->result       = new PreCommitResult();
        $this->reportWriter = new PreCommitReportWriter(
            $this->config->getReportPath(),
            $this->config->getTodoPath()
        );
        $this->files        = $files ?? [];
        $this->touchedOnly  = $touchedOnly;
    }

    /**
     * Run the PreCommit discipline check
     */
    public function run() : PreCommitResult
    {
        $startTime = microtime(true);

        // Build context
        $context = $this->buildContext();

        // Run all enabled checks
        $this->runChecks($context);

        // Determine final status
        $this->result->setExecutionTime(microtime(true) - $startTime);
        $this->result->canCommit();

        // Save reports if not dry-run
        if (! $this->config->isDryRun()) {
            $this->reportWriter->writeReport($this->result);
            $this->reportWriter->writeTodo($this->result);
        }

        return $this->result;
    }

    /**
     * Build context for checks.
     *
     * @return array<string, mixed>
     */
    private function buildContext() : array
    {
        $basePath = getcwd() ?: '.';

        // If no files provided, detect from git
        $files = $this->files;
        if (empty($files)) {
            $files = $this->detectStagedFiles($basePath);
        }

        return [
            'files'        => $files,
            'base_path'    => $basePath,
            'system_root'  => $this->detectSystemRoot($basePath),
            'touched_only' => $this->touchedOnly,
            'config'       => $this->config,
            'timestamp'    => date('c'),
            'git_branch'   => $this->getGitBranch($basePath),
        ];
    }

    /**
     * Detect staged files from git.
     *
     * @return list<string>
     */
    private function detectStagedFiles(string $basePath) : array
    {
        $gitBin = $this->findGitBinary($basePath);

        if ($this->touchedOnly) {
            exec("{$gitBin} diff --cached --name-only --diff-filter=ACM 2>/dev/null", $output, $returnVar);
        } else {
            exec("{$gitBin} diff --name-only 2>/dev/null", $output, $returnVar);
        }

        if ($returnVar !== 0 || empty($output)) {
            return [];
        }

        return array_values(array_filter($output));
    }

    /**
     * Find git binary
     */
    private function findGitBinary(string $basePath) : string
    {
        $locations = ['/usr/bin/git', '/usr/local/bin/git', '/bin/git', 'git'];

        foreach ($locations as $loc) {
            if (is_executable($loc)) {
                return $loc;
            }
        }

        exec('command -v git 2>/dev/null', $output, $returnVar);
        if ($returnVar === 0 && ! empty($output)) {
            return trim($output[0]);
        }

        return 'git';
    }

    /**
     * Detect system root directory
     */
    private function detectSystemRoot(string $basePath) : string
    {
        $candidates = ['components', 'framework', 'src', 'system', 'System', 'app'];

        foreach ($candidates as $candidate) {
            $path = $basePath . '/' . $candidate;
            if (is_dir($path)) {
                return $path;
            }
        }

        return $basePath;
    }

    /**
     * Get current git branch
     */
    private function getGitBranch(string $basePath) : string
    {
        $gitBin = $this->findGitBinary($basePath);
        exec("{$gitBin} rev-parse --abbrev-ref HEAD 2>/dev/null", $output, $returnVar);

        return $returnVar === 0 ? ($output[0] ?? 'unknown') : 'unknown';
    }

    /**
     * Run all enabled checks in order.
     *
     * @param array<string, mixed> $context
     */
    private function runChecks(array $context) : void
    {
        $checks = [
            // Blocking checks first
            'CheckPhpSyntax'               => CheckPhpSyntax::class,
            'CheckNamingConventions'       => CheckNamingConventions::class,
            'CheckHowToRules'              => CheckHowToRules::class,
            'CheckForbiddenWords'          => CheckForbiddenWords::class,
            'CheckFileStructure'           => CheckFileStructure::class,

            // Warnings next
            'DetectLegacyCode'             => DetectLegacyCode::class,
            'DetectDeprecatedCode'         => DetectDeprecatedCode::class,
            'DetectLegacyAliases'          => DetectLegacyAliases::class,
            'DetectTodoComments'           => DetectTodoComments::class,
            'DetectToolingScripts'         => DetectToolingScripts::class,
            'CheckPublicSurfaceRules'      => CheckPublicSurfaceRules::class,

            // Architecture and external tools last
            'DetectArchitectureViolations' => DetectArchitectureViolations::class,
            'RunExternalToolingScripts'    => RunExternalToolingScripts::class,
        ];

        foreach ($checks as $checkName => $checkClass) {
            if (! $this->config->isCheckEnabled($checkName)) {
                continue;
            }

            try {
                $check  = new $checkClass($this->config);
                $issues = $check->run($context);

                if (empty($issues)) {
                    $this->result->addPassedCheck($checkName);
                } else {
                    foreach ($issues as $issue) {
                        $this->result->addIssue($issue);
                    }
                }
            } catch (Throwable $e) {
                // Log error but continue with other checks
                $this->result->addIssue(new PreCommitIssue(
                                            $checkName,
                                            PreCommitIssue::SEVERITY_ERROR,
                                            "Check failed: " . $e->getMessage(),
                                            null,
                                            null,
                                            'CHECK_ERROR'
                                        ));
            }
        }
    }

    public function getResult() : PreCommitResult
    {
        return $this->result;
    }

    public function getReportWriter() : PreCommitReportWriter
    {
        return $this->reportWriter;
    }

    public function getConfig() : PreCommitConfig
    {
        return $this->config;
    }
}
