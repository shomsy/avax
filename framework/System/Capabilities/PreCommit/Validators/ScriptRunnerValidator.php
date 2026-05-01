<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Script Runner Validator
 *
 * Dynamically discovers and executes scripts from the tooling/ directory.
 * Supports multiple script types: PHP, Python, Go, NodeJS, Shell (.sh)
 *
 * This validator scans for scripts and executes them as part of the
 * pre-commit validation chain, providing structured results.
 */
class ScriptRunnerValidator extends BaseValidator
{
    /** @var array<string, array> */
    private static array $scriptExecutors
        = [
            'php'     => [
                'command'   => 'php',
                'extension' => '.php',
                'timeout'   => 30,
            ],
            'python'  => [
                'command'   => 'python3',
                'extension' => '.py',
                'timeout'   => 30,
            ],
            'python2' => [
                'command'   => 'python',
                'extension' => '.py',
                'timeout'   => 30,
            ],
            'shell'   => [
                'command'   => 'bash',
                'extension' => '.sh',
                'timeout'   => 30,
            ],
            'go'      => [
                'command'   => 'go run',
                'extension' => '.go',
                'timeout'   => 60,
            ],
            'nodejs'  => [
                'command'   => 'node',
                'extension' => '.js',
                'timeout'   => 30,
            ],
        ];

    private string $toolingPath;
    /** @var array<string> */
    private array $enabledScripts;
    private bool  $discoverScripts;

    /**
     * @param array<string>|null $enabledScripts Scripts to enable, null for all discovered
     */
    public function __construct(
        ?string $toolingPath = null,
        ?array  $enabledScripts = null,
        bool    $discoverScripts = true
    )
    {
        parent::__construct('ScriptRunnerValidator');
        $this->toolingPath     = $toolingPath ?? (getcwd() . '/tooling');
        $this->enabledScripts  = $enabledScripts ?? [];
        $this->discoverScripts = $discoverScripts;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function validate(array $context) : ValidationResult
    {
        $messages  = [];
        $allPassed = true;

        if (! is_dir($this->toolingPath)) {
            return $this->passToNext($context);
        }

        $scripts = $this->discoverScripts();

        if (empty($scripts)) {
            $messages[] = "No scripts found in tooling/ directory";

            return new ValidationResult(
                true,
                $messages,
                'info',
                null,
                null,
                'SCRIPT_RUNNER_001'
            );
        }

        foreach ($scripts as $script) {
            $result = $this->executeScript($script);

            if (! $result->isPassed()) {
                $allPassed = false;
                $messages  = array_merge($messages, $result->getMessages());
            } else {
                $messages = array_merge($messages, $result->getMessages());
            }
        }

        $result = new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'warning',
            null,
            null,
            'SCRIPT_RUNNER_001'
        );

        return $this->combineWithNext($context, $result);
    }

    /**
     * Discover scripts in the tooling directory
     *
     * @return array<string, array>
     */
    private function discoverScripts() : array
    {
        $scripts    = [];
        $extensions = array_column(self::$scriptExecutors, 'extension');

        // Scan tooling directory recursively
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->toolingPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $pathname  = $file->getPathname();
            $extension = $file->getExtension();

            // Check if file extension matches our executors
            $executorType = $this->matchExtension($extension, $pathname);
            if ($executorType === null) {
                continue;
            }

            $scriptName = basename($pathname);

            // Filter by enabled scripts if specified
            if (! empty($this->enabledScripts) && ! in_array($scriptName, $this->enabledScripts)) {
                continue;
            }

            // Skip non-executable scripts or special files
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

    /**
     * Match file extension to executor type
     */
    private function matchExtension(string $extension, string $pathname) : ?string
    {
        foreach (self::$scriptExecutors as $type => $config) {
            $ext = ltrim($config['extension'], '.');
            if ($extension === $ext || $extension === $ext . '3') {
                return $type;
            }
        }

        return null;
    }

    /**
     * Check if script should be skipped
     */
    private function shouldSkip(string $scriptName) : bool
    {
        $skipPatterns = [
            '/^_/',
            '/\.bak$/',
            '/^README/',
            '/^TODO/',
            '/\.md$/',
        ];

        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, $scriptName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Execute a script and return the result
     */
    private function executeScript(array $script) : ValidationResult
    {
        $scriptType = $script['type'];
        $scriptPath = $script['path'];
        $scriptName = $script['name'];

        if (! isset(self::$scriptExecutors[$scriptType])) {
            return ValidationResult::fail(
                "Unknown script type: {$scriptType}",
                'error',
                $scriptName,
                null,
                'SCRIPT_TYPE_001'
            );
        }

        $executor = self::$scriptExecutors[$scriptType];
        $command  = $executor['command'] . ' ' . escapeshellarg($scriptPath);
        $timeout  = $executor['timeout'];

        // Execute the script
        $output   = $this->runCommand($command, $timeout);
        $exitCode = $output['exit_code'];
        $stdout   = $output['stdout'];
        $stderr   = $output['stderr'];

        // Analyze the output
        return $this->analyzeOutput($scriptName, $exitCode, $stdout, $stderr);
    }

    /**
     * Run command with timeout
     *
     * @return array{stdout: string, stderr: string, exit_code: int}
     */
    private function runCommand(string $command, int $timeout) : array
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes, getcwd());

        if (! is_resource($process)) {
            return [
                'stdout'    => '',
                'stderr'    => 'Failed to execute command',
                'exit_code' => 1,
            ];
        }

        // Set timeout
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stdout    = '';
        $stderr    = '';
        $startTime = time();

        while ( ! feof($pipes[1]) || ! feof($pipes[2]) ) {
            // Check timeout
            if (time() - $startTime > $timeout) {
                proc_terminate($process, SIGTERM);

                return [
                    'stdout'    => $stdout,
                    'stderr'    => "Script execution timed out after {$timeout}s",
                    'exit_code' => 124,
                ];
            }

            $read   = [$pipes[1], $pipes[2]];
            $write  = [];
            $except = null;

            $ready = @stream_select($read, $write, $except, 0, 200000);
            if ($ready === false) {
                break;
            }

            if (! empty($read)) {
                foreach ($read as $pipe) {
                    if ($pipe === $pipes[1]) {
                        $stdout .= fread($pipe, 8192);
                    } elseif ($pipe === $pipes[2]) {
                        $stderr .= fread($pipe, 8192);
                    }
                }
            }

            // Check if process ended
            $status = proc_get_status($process);
            if (! $status['running']) {
                break;
            }
        }

        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        proc_close($process);

        return [
            'stdout'    => $stdout,
            'stderr'    => $stderr,
            'exit_code' => $status['exitcode'] ?? 0,
        ];
    }

    /**
     * Analyze script output for pass/fail
     */
    private function analyzeOutput(
        string $scriptName,
        int    $exitCode,
        string $stdout,
        string $stderr
    ) : ValidationResult
    {
        $messages = [];
        $passed   = true;
        $severity = 'info';

        // Combine stdout and stderr for analysis
        $combinedOutput = $stdout . "\n" . $stderr;

        // Check exit code
        if ($exitCode !== 0) {
            $passed     = false;
            $severity   = 'error';
            $messages[] = sprintf(
                "[%s] Script failed with exit code %d",
                $scriptName,
                $exitCode
            );
        }

        // Check for error patterns in output
        $errorPatterns = [
            'CRITICAL'  => ['pattern' => 'CRITICAL', 'severity' => 'error'],
            'ERROR'     => ['pattern' => '\bERROR\b', 'severity' => 'error'],
            'FAIL'      => ['pattern' => 'FAIL', 'severity' => 'error'],
            'MISSING'   => ['pattern' => 'MISSING', 'severity' => 'warning'],
            'EXCEPTION' => ['pattern' => 'Exception', 'severity' => 'error'],
            'WARNING'   => ['pattern' => '\bWARNING\b', 'severity' => 'warning'],
            '❌'         => ['pattern' => '❌', 'severity' => 'error'],
        ];

        foreach ($errorPatterns as $key => $config) {
            if (preg_match('/' . $config['pattern'] . '/', $combinedOutput)) {
                $passed = false;
                if ($config['severity'] === 'error') {
                    $severity = 'error';
                }

                // Extract relevant line for message
                $lines     = explode("\n", $combinedOutput);
                $foundLine = false;
                foreach ($lines as $line) {
                    if (preg_match('/' . $config['pattern'] . '/', $line)) {
                        $msg = trim($line);
                        if (strlen($msg) > 100) {
                            $msg = substr($msg, 0, 97) . '...';
                        }
                        $messages[] = sprintf("[%s] %s", $scriptName, $msg);
                        $foundLine  = true;
                        break;
                    }
                }

                if (! $foundLine) {
                    $messages[] = sprintf(
                        "[%s] %s detected in output",
                        $scriptName,
                        $key
                    );
                }
            }
        }

        // If passed, add success message
        if ($passed) {
            $messages[] = sprintf("[%s] ✅ Passed", $scriptName);
        }

        return new ValidationResult(
            $passed,
            $messages,
            $severity,
            null,
            null,
            'SCRIPT_' . strtoupper(preg_replace('/[^A-Z]/', '', $scriptName)) . '_001'
        );
    }

    /**
     * Enable specific scripts
     *
     * @param array<string> $scripts
     */
    public function enableScripts(array $scripts) : self
    {
        $this->enabledScripts = $scripts;

        return $this;
    }

    /**
     * Disable script discovery, use only enabled scripts
     */
    public function disableDiscovery() : self
    {
        $this->discoverScripts = false;

        return $this;
    }

    /**
     * Add custom script executor
     *
     * @param array{command: string, extension: string, timeout: int} $config
     */
    public function addExecutor(string $type, array $config) : self
    {
        self::$scriptExecutors[$type] = $config;

        return $this;
    }

    public function supports(array $context) : bool
    {
        return is_dir($this->toolingPath);
    }
}
