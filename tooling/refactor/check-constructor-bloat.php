<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Scans for constructor bloat — constructors with too many dependencies.
 *
 * Rules:
 * - 0-4 parameters: normal
 * - 5-7 parameters: check — may indicate responsibility creep
 * - 8+ parameters: warning — likely violates SRP
 *
 * This tool reports constructors that exceed thresholds.
 * It does not auto-fix — human judgment is required.
 */
final class CheckConstructorBloat
{
    private int $warningThreshold = 8;
    private int $checkThreshold   = 5;

    private array $scanRoots
        = [
            'framework/System',
            'components',
        ];

    private array $allowedContexts
        = [
            '/tests/',
            '/Tests/',
            '/tooling/',
            '/Tooling/',
            '/Application/Container/',
        ];

    private array $warnings = [];

    public function check() : array
    {
        $basePath = dirname(__DIR__, 2);

        foreach ($this->scanRoots as $root) {
            $fullPath = $basePath . '/' . $root;
            $this->scanDirectory($fullPath, $basePath);
        }

        return [
            'status'   => $this->warnings === [] ? 'PASS' : 'FAIL',
            'warnings' => $this->warnings,
        ];
    }

    private function scanDirectory(string $directory, string $basePath) : void
    {
        if (! is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath . '/', '', $file->getPathname());

            if ($this->isInAllowedContext($relativePath)) {
                continue;
            }

            $this->scanFile($file, $relativePath);
        }
    }

    private function isInAllowedContext(string $relativePath) : bool
    {
        foreach ($this->allowedContexts as $context) {
            if (str_contains($relativePath, $context)) {
                return true;
            }
        }

        return false;
    }

    private function scanFile(SplFileInfo $file, string $relativePath) : void
    {
        $content = file_get_contents($file->getPathname());
        $lines   = explode("\n", $content);

        // Find __construct methods and count promoted parameters
        $inConstruct    = false;
        $constructStart = 0;
        $braceCount     = 0;

        foreach ($lines as $lineNumber => $line) {
            if (preg_match('/public\s+function\s+__construct\s*\(/', $line)) {
                $inConstruct    = true;
                $constructStart = $lineNumber;
                $braceCount     = 0;
            }

            if ($inConstruct) {
                $braceCount += substr_count($line, '{') - substr_count($line, '}');

                if ($braceCount === 0 && str_contains($line, ')')) {
                    // Count constructor parameters
                    $paramCount = $this->countConstructorParams($lines, $constructStart, $lineNumber);

                    if ($paramCount >= $this->warningThreshold) {
                        $this->warnings[] = sprintf(
                            '%s:%d — WARNING: Constructor has %d parameters (threshold: %d)',
                            $relativePath,
                            $constructStart + 1,
                            $paramCount,
                            $this->warningThreshold,
                        );
                    } elseif ($paramCount >= $this->checkThreshold) {
                        $this->warnings[] = sprintf(
                            '%s:%d — CHECK: Constructor has %d parameters (threshold: %d)',
                            $relativePath,
                            $constructStart + 1,
                            $paramCount,
                            $this->checkThreshold,
                        );
                    }

                    $inConstruct = false;
                }
            }
        }
    }

    private function countConstructorParams(array $lines, int $start, int $end) : int
    {
        $constructSignature = '';
        for ($i = $start; $i <= $end; $i++) {
            $constructSignature .= ' ' . trim($lines[$i]);
        }

        // Extract content between ( and )
        if (preg_match('/__construct\s*\((.*?)\)/s', $constructSignature, $matches)) {
            $params = $matches[1];
            if (trim($params) === '') {
                return 0;
            }

            // Count comma-separated parameters (promoted properties count as 1 each)
            return substr_count($params, ',') + 1;
        }

        return 0;
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckConstructorBloat();
    $result  = $checker->check();

    echo $result['status'] . "\n";

    if (! empty($result['warnings'])) {
        echo implode("\n", $result['warnings']) . "\n";
        exit(1);
    }

    exit(0);
}
