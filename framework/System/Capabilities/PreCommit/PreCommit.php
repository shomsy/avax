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
final readonly class PreCommit
{
    private PreCommitConfig $preCommitConfig;

    private PreCommitResult $preCommitResult;

    private PreCommitReportWriter $preCommitReportWriter;

    /** @var list<string> */
    private array                 $files;

    /**
     * @param list<string>|null $files
     */
    public function __construct(
        ?PreCommitConfig $preCommitConfig = null,
        ?array           $files = null,
        private bool     $touchedOnly = true
    )
    {
        $this->preCommitConfig       = $preCommitConfig ?? new PreCommitConfig();
        $this->preCommitResult       = new PreCommitResult();
        $this->preCommitReportWriter = new PreCommitReportWriter(
            $this->preCommitConfig->getReportPath(),
            $this->preCommitConfig->getTodoPath()
        );
        $this->files        = $files ?? [];
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
        $this->preCommitResult->setExecutionTime(microtime(true) - $startTime);
        $this->preCommitResult->canCommit();

        // Save reports if not dry-run
        if (! $this->preCommitConfig->isDryRun()) {
            $this->preCommitReportWriter->writeReport($this->preCommitResult);
            $this->preCommitReportWriter->writeTodo($this->preCommitResult);
        }

        return $this->preCommitResult;
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
        if ($files === []) {
            $files = $this->detectStagedFiles();
        }

        return [
            'files'        => $files,
            'base_path'    => $basePath,
            'system_root'  => $this->detectSystemRoot($basePath),
            'touched_only' => $this->touchedOnly,
            'config'     => $this->preCommitConfig,
            'timestamp'    => date('c'),
            'git_branch' => $this->getGitBranch(),
        ];
    }

    /**
     * Detect staged files from git.
     *
     * @return list<string>
     */
    private function detectStagedFiles() : array
    {
        $gitBin = $this->findGitBinary();
        if ($this->touchedOnly) {
            exec($gitBin . ' diff --cached --name-only --diff-filter=ACM 2>/dev/null', $output, $returnVar);
        } else {
            exec($gitBin . ' diff --name-only 2>/dev/null', $output, $returnVar);
        }
        if ($returnVar !== 0 || $output === []) {
            return [];
        }
        return array_values(array_filter($output));
    }

    /**
     * Find git binary
     */
    private function findGitBinary() : string
    {
        $locations = ['/usr/bin/git', '/usr/local/bin/git', '/bin/git', 'git'];
        foreach ($locations as $location) {
            if (is_executable($location)) {
                return $location;
            }
        }
        exec('command -v git 2>/dev/null', $output, $returnVar);
        if ($returnVar === 0 && $output !== []) {
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
    private function getGitBranch() : string
    {
        $gitBin = $this->findGitBinary();
        exec($gitBin . ' rev-parse --abbrev-ref HEAD 2>/dev/null', $output, $returnVar);
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
            if (! $this->preCommitConfig->isCheckEnabled($checkName)) {
                continue;
            }

            try {
                $check = new $checkClass($this->preCommitConfig);
                $issues = $check->run($context);

                if ($issues === []) {
                    $this->preCommitResult->addPassedCheck($checkName);
                } else {
                    foreach ($issues as $issue) {
                        $this->preCommitResult->addIssue($issue);
                    }
                }
            } catch (Throwable $e) {
                // Log error but continue with other checks
                $this->preCommitResult->addIssue(new PreCommitIssue(
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
        return $this->preCommitResult;
    }

    public function getReportWriter() : PreCommitReportWriter
    {
        return $this->preCommitReportWriter;
    }

    public function getConfig() : PreCommitConfig
    {
        return $this->preCommitConfig;
    }
}
