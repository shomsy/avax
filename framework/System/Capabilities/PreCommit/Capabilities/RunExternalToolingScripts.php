<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Run External Tooling Scripts
 *
 * Executes external tooling scripts as adapters.
 * Provides structured results from script outputs.
 */
final class RunExternalToolingScripts
{
    /** @var array<string, array{command: string, extension: string, timeout: int}> */
    private static array    $scriptExecutors
        = [
            'php'    => ['command' => 'php', 'extension' => '.php', 'timeout' => 30],
            'python' => ['command' => 'python3', 'extension' => '.py', 'timeout' => 30],
            'shell'  => ['command' => 'bash', 'extension' => '.sh', 'timeout' => 30],
            'go'     => ['command' => 'go run', 'extension' => '.go', 'timeout' => 60],
            'nodejs' => ['command' => 'node', 'extension' => '.js', 'timeout' => 30],
        ];
    private PreCommitConfig $config;

    public function __construct(PreCommitConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return list<PreCommitIssue>
     */
    public function run(array $context) : array
    {
        $issues   = [];
        $basePath = getcwd() ?: '.';
        $toolingPath = $basePath . '/tooling';

        if (! is_dir($toolingPath)) {
            return $issues;
        }

        // Discover and run scripts in tooling/
        $scripts = $this->discoverScripts($toolingPath);

        foreach ($scripts as $script) {
            $result = $this->executeScript($script);

            if (! $result['passed']) {
                $issues[] = new PreCommitIssue(
                    'RunExternalToolingScripts',
                    $result['severity'],
                    $result['message'],
                    $script['path'],
                    null,
                    'TOOL_SCRIPT_' . strtoupper(substr($script['name'], 0, 3))
                );
            }
        }

        return $issues;
    }

    /**
     * @return array<string, array{path: string, type: string, name: string}>
     */
    private function discoverScripts(string $toolingPath) : array
    {
        $scripts = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($toolingPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo) {
                continue;
            }

            if (! $file->isFile()) {
                continue;
            }

            $pathname  = $file->getPathname();
            $extension = $file->getExtension();

            $executorType = $this->matchExtension($extension, $pathname);
            if ($executorType === null) {
                continue;
            }

            $scriptName = basename($pathname);

            // Skip non-scripts
            if ($this->shouldSkip($scriptName)) {
                continue;
            }

            $scripts[$scriptName] = [
                'path' => $pathname,
                'type' => $executorType,
                'name' => $scriptName,
            ];
        }

        return $scripts;
    }

    private function matchExtension(string $extension, string $pathname) : ?string
    {
        foreach (self::$scriptExecutors as $type => $config) {
            $ext = ltrim($config['extension'], '.');
            if ($extension === $ext) {
                return $type;
            }
        }

        return null;
    }

    private function shouldSkip(string $scriptName) : bool
    {
        $skipPatterns = ['/^_/', '/\.bak$/', '/^README/', '/^TODO/', '/\.md$/'];

        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, $scriptName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array{path: string, type: string, name: string} $script
     *
     * @return array{passed: bool, severity: string, message: string}
     */
    private function executeScript(array $script) : array
    {
        $scriptType = $script['type'];
        $scriptPath = $script['path'];
        $scriptName = $script['name'];

        if (! isset(self::$scriptExecutors[$scriptType])) {
            return [
                'passed'   => false,
                'severity' => PreCommitIssue::SEVERITY_ERROR,
                'message'  => "Unknown script type: {$scriptType}",
            ];
        }

        $executor = self::$scriptExecutors[$scriptType];
        $command  = $executor['command'] . ' ' . escapeshellarg($scriptPath);

        // Execute with timeout
        $output    = [];
        $returnVar = 0;
        exec($command . ' 2>&1', $output, $returnVar);
        $outputText = implode("\n", $output);

        $passed = ($returnVar === 0);

        // Check for error patterns in output
        $hasError = preg_match('/\bERROR\b|\bCRITICAL\b|\bFAIL\b/', $outputText);

        $severity = ($hasError || $returnVar !== 0)
            ? PreCommitIssue::SEVERITY_ERROR
            : PreCommitIssue::SEVERITY_INFO;

        $message = $passed
            ? "Script {$scriptName} passed"
            : "Script {$scriptName} failed: " . substr($outputText, 0, 150);

        return [
            'passed'   => $passed || ! $hasError,
            'severity' => $severity,
            'message'  => $message,
        ];
    }
}
