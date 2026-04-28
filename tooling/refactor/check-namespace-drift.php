<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

final class CheckNamespaceDrift
{
    private const FORBIDDEN_NAMESPACES = [
        'namespace components\\'         => 'Use Avax\\Components\\<Suite>\\<Component>\\System\\... namespace',
        'namespace Avax\\DataFoundation' => 'Use Avax\\Components\\DataStack\\Data\\System\\... namespace (or a bridge-only file)',
        'namespace Avax\\DataLayer'      => 'Use Avax\\Components\\DataStack\\Persistence\\System\\... namespace (or a bridge-only file)',
    ];

    private const ALLOWED_BRIDGE_PATHS = [
        'components/DataFoundation/',
    ];

    private const PHP_EXTENSIONS = ['.php'];

    private const EXCEPTIONS = [
        'CompileContainer.php' => 'Known: generates dynamic code with legacy namespace',
    ];

    private array $violations = [];
    private array $checkedFiles = [];

    public function check(string $rootPath = 'components'): array
    {
        $this->violations = [];
        $this->checkedFiles = [];

        $rootPath = realpath($rootPath);
        if ($rootPath === false) {
            throw new RuntimeException("Path does not exist: $rootPath");
        }

        $dirIterator = new RecursiveDirectoryIterator($rootPath);
        $iterator = new RecursiveIteratorIterator(
            $dirIterator,
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();
            $this->checkedFiles[] = $filePath;
            $this->checkFile($filePath);
        }

        return [
            'checked_files' => count($this->checkedFiles),
            'violations' => count($this->violations),
            'details' => $this->violations,
        ];
    }

    private function checkFile(string $filePath): void
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            return;
        }

        if (!$this->isPhpFile($content)) {
            return;
        }

        if ($this->isBridgeFile($filePath)) {
            return;
        }

        if ($this->isGeneratedCodeFile($filePath)) {
            return;
        }

        $inLegacyFolder = $this->isInLegacyFolder($filePath);

        foreach (self::FORBIDDEN_NAMESPACES as $forbidden => $message) {
            if ($this->containsNamespace($content, $forbidden)) {
                if ($inLegacyFolder && $this->isLegacyNamespaceAllowed($forbidden)) {
                    continue;
                }
                if ($this->isException($filePath)) {
                    continue;
                }
                $this->violations[] = [
                    'file' => $filePath,
                    'forbidden_pattern' => $forbidden,
                    'suggestion' => $message,
                ];
            }
        }
    }

    private function isPhpFile(string $content): bool
    {
        return str_starts_with(trim($content), '<?php');
    }

    private function isBridgeFile(string $filePath): bool
    {
        foreach (self::ALLOWED_BRIDGE_PATHS as $bridgePath) {
            if (str_contains($filePath, $bridgePath)) {
                return true;
            }
        }
        return false;
    }

    private function isInLegacyFolder(string $filePath): bool
    {
        foreach (self::ALLOWED_BRIDGE_PATHS as $bridgePath) {
            if (str_contains($filePath, $bridgePath)) {
                return true;
            }
        }
        return false;
    }

    private function isGeneratedCodeFile(string $filePath): bool
    {
        return str_contains($filePath, '/Generated/');
    }

    private function isLegacyNamespaceAllowed(string $forbiddenNamespace): bool
    {
        foreach (['Avax\\DataFoundation', 'Avax\\DataLayer'] as $legacyNs) {
            if (str_contains($forbiddenNamespace, $legacyNs)) {
                return true;
            }
        }
        return false;
    }

    private function isException(string $filePath): bool
    {
        foreach (self::EXCEPTIONS as $exceptionFile => $reason) {
            if (str_contains($filePath, $exceptionFile)) {
                return true;
            }
        }
        return false;
    }

    private function containsNamespace(string $content, string $namespace): bool
    {
        return str_contains($content, $namespace);
    }
}

if (php_sapi_name() === 'cli' && isset($argv[0])) {
    $checker = new CheckNamespaceDrift();
    $rootPath = $argv[1] ?? 'components';
    error_reporting(E_ALL);
    $results = $checker->check($rootPath);

    echo "Namespace Drift Checker\n";
    echo "=====================\n\n";
    echo "Root path: {$rootPath}\n";
    echo "Checked files: {$results['checked_files']}\n";
    echo "Violations: {$results['violations']}\n\n";

    if ($results['violations'] > 0) {
        echo "VIOLATIONS FOUND:\n";
        echo str_repeat('-', 60) . "\n";

        $seenFile = null;

        foreach ($results['details'] as $violation) {
            $file = $violation['file'];
            if ($file === $seenFile) {
                continue;
            }
            $seenFile = $file;

            echo "File: {$file}\n";
            echo "Forbidden: {$violation['forbidden_pattern']}\n";
            echo "Suggestion: {$violation['suggestion']}\n\n";
        }

        exit(1);
    }

    echo "No violations found.\n";
    exit(0);
}