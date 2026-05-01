<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit;

use Avax\Framework\System\Capabilities\PreCommit\Report\ReportStorage;
use Avax\Framework\System\Capabilities\PreCommit\Todo\TodoGenerator;
use Avax\Framework\System\Capabilities\PreCommit\ValidationChain\ValidationChain;
use Avax\Framework\System\Capabilities\PreCommit\Validators\DeprecatedCodeValidator;
use Avax\Framework\System\Capabilities\PreCommit\Validators\FileStructureValidator;
use Avax\Framework\System\Capabilities\PreCommit\Validators\HowToRulesValidator;
use Avax\Framework\System\Capabilities\PreCommit\Validators\LegacyCodeValidator;
use Avax\Framework\System\Capabilities\PreCommit\Validators\NamingConventionValidator;
use Avax\Framework\System\Capabilities\PreCommit\Validators\PhpSyntaxValidator;
use Avax\Framework\System\Capabilities\PreCommit\Validators\SecurityValidator;
use Avax\Framework\System\Capabilities\PreCommit\Validators\TodoCommentValidator;
use Avax\Framework\System\Capabilities\PreCommit\Validators\ToolingIntegrationValidator;

/**
 * Pre-Commit Validation CLI
 * 
 * Entry point for pre-commit validation chain.
 * Validates staged files against architectural and coding standards.
 */
class PreCommitValidator
{
    private ValidationChain $chain;
    private ValidationReport $report;
    private ReportStorage $storage;
    private array $options;

    public function __construct(array $argv)
    {
        $this->options = $this->parseOptions($argv);
        $this->report = new ValidationReport();
        $this->storage = new ReportStorage();
        $this->chain = new ValidationChain();
        $this->configureChain();
    }

    private function parseOptions(array $argv): array
    {
        $options = [
            'format' => 'text',
            'staged' => true,
            'save-report' => true,
            'generate-todo' => true,
            'help' => false,
            'files' => null,
            'staged-files-file' => null,
        ];

        array_shift($argv);

        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                $parts = explode('=', substr($arg, 2), 2);
                $key = $parts[0];
                $value = $parts[1] ?? true;
                if (array_key_exists($key, $options)) {
                    $options[$key] = $value;
                }
            } elseif ($arg === '-h' || $arg === '--help') {
                $options['help'] = true;
            }
        }

        return $options;
    }

    private function configureChain(): void
    {
        $this->chain->add(new NamingConventionValidator())
                   ->add(new HowToRulesValidator())
                   ->add(new PhpSyntaxValidator())
                   ->add(new FileStructureValidator())
            ->add(new SecurityValidator())
            ->add(new LegacyCodeValidator())
            ->add(new DeprecatedCodeValidator())
            ->add(new TodoCommentValidator())
            ->add(new ToolingIntegrationValidator());
    }

    public function run(): int
    {
        if ($this->options['help']) {
            $this->printHelp();
            return 0;
        }

        $startTime = microtime(true);
        $context = $this->buildContext();

        if (empty($context['staged_files'])) {
            echo "ℹ No staged files to validate.\n";
            return 0;
        }

        echo "🔍 Running pre-commit validation...\n";
        echo "   Files to validate: " . count($context['staged_files']) . "\n\n";

        $result = $this->chain->validate($context);
        $this->report->addResult($result);
        $this->report->addMetadata('context', $context);
        $this->report->addMetadata('validators', array_map(fn($v) => $v->getName(), $this->chain->getValidators()));
        $this->report->setExecutionTime(microtime(true) - $startTime);

        if ($this->options['save-report']) {
            $this->storage->save($this->report, (bool)$this->options['generate-todo']);
        }

        $this->outputResults();
        return $result->isPassed() ? 0 : 1;
    }

    private function buildContext(): array
    {
        $files = [];
        $basePath = getcwd();

        if (isset($this->options['files'])) {
            $files = is_array($this->options['files'])
                ? $this->options['files']
                : array_filter(explode(',', $this->options['files']));
        } elseif (isset($this->options['staged-files-file'])) {
            $content = file_get_contents($this->options['staged-files-file']);
            if ($content !== false) {
                $files = array_filter(array_map('trim', explode("\n", $content)));
            }
        } elseif ($this->options['staged']) {
            $gitBin = $this->findGitBinary();
            exec("{$gitBin} diff --cached --name-only --diff-filter=ACM 2>/dev/null", $output, $returnVar);
            if ($returnVar === 0 && !empty($output)) {
                $files = array_filter($output);
            }
        } else {
            $gitBin = $this->findGitBinary();
            exec("{$gitBin} diff --name-only 2>/dev/null", $output, $returnVar);
            if ($returnVar === 0) {
                $files = $output;
            }
        }

        return [
            'staged_files' => $files,
            'base_path' => $basePath,
            'system_root' => $this->detectSystemRoot($basePath),
            'timestamp' => date('c'),
            'git_branch' => $this->getGitBranch(),
        ];
    }

    private function getGitBranch(): string
    {
        $gitBin = $this->findGitBinary();
        exec("{$gitBin} rev-parse --abbrev-ref HEAD 2>/dev/null", $output, $returnVar);
        return $returnVar === 0 ? ($output[0] ?? 'unknown') : 'unknown';
    }

    private function findGitBinary(): string
    {
        if (!empty($_ENV['GIT_BINARY'])) {
            return $_ENV['GIT_BINARY'];
        }

        $locations = [
            '/usr/bin/git', '/usr/local/bin/git', '/bin/git', '/usr/lib/git-core/git',
        ];

        foreach ($locations as $loc) {
            if (is_executable($loc)) {
                return $loc;
            }
        }

        exec('command -v git 2>/dev/null', $output, $returnVar);
        if ($returnVar === 0 && !empty($output)) {
            return trim($output[0]);
        }

        return 'git';
    }

    private function detectSystemRoot(string $basePath): string
    {
        $candidates = ['src', 'system', 'System', 'product', 'app', 'framework'];
        foreach ($candidates as $candidate) {
            if (is_dir($basePath . '/' . $candidate)) {
                return $basePath . '/' . $candidate;
            }
        }
        return $basePath;
    }

    private function outputResults(): void
    {
        if ($this->options['format'] === 'json') {
            $data = $this->report->toArray();
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                echo "JSON encoding failed: " . json_last_error_msg() . "\n";
                echo var_export($data, true) . "\n";
            } else {
                echo $json . "\n";
            }
        } else {
            echo $this->report->getSummaryText();
        }
    }

    private function printHelp(): void
    {
        echo "Avax Pre-Commit Validation System\n";
        echo "==================================\n\n";
        echo "Usage: php avax validate [options]\n\n";
        echo "Options:\n";
        echo "  --format=FORMAT      Output format: text|json (default: text)\n";
        echo "  --staged             Validate staged files via git (default: true)\n";
        echo "  --files=LIST         Comma-separated file list\n";
        echo "  --staged-files-file  File containing list of staged files\n";
        echo "  --save-report        Save report to file (default: true)\n";
        echo "  --generate-todo      Generate TODO items from failures (default: true)\n";
        echo "  -h, --help           Show this help\n\n";
        echo "Examples:\n";
        echo "  php avax validate\n";
        echo "  php avax validate --format=json\n";
        echo "  php avax validate --staged-files-file=files.txt\n\n";
        echo "Validation Chain:\n";
        echo "  1. Naming Convention Validator\n";
        echo "  2. How-To Rules Validator\n";
        echo "  3. PHP Syntax Validator\n";
        echo "  4. File Structure Validator\n";
        echo "  5. Security Validator\n";
        echo "  6. Legacy Code Validator\n";
        echo "  7. Deprecated Code Validator\n";
        echo "  8. TODO Comment Validator\n";
        echo "  9. Tooling Integration Validator\n\n";
        echo "Reports: .agents/reports/validation/\n";
        echo "TODO items: .agents/management/TODO.md\n";
    }
}
