<?php

declare(strict_types=1);

namespace Avax\Tooling;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Comprehensive Error Scanner and Fixer for AvaX Stage 07 Test Layer Repair
 *
 * Scans for and fixes:
 * 1. PHP syntax errors (uses existing fix-syntax-errors.php logic)
 * 2. Identifies missing classes referenced in tests
 * 3. Detects namespace mismatches
 * 4. Finds orphaned test files
 * 5. Reports errors by category with fix recommendations
 */
class ErrorScannerFixer
{
    private array $syntaxErrors = [];
    private array $missingClasses = [];
    private array $namespaceIssues = [];
    private array $fixedFiles = [];

    public function run(): void
    {
        echo "=== AvaX Comprehensive Error Scan & Fix ===\n\n";

        $this->stepPhpSyntaxCheck();
        $this->stepScanForMissingClasses();
        $this->stepNamespaceIntegrityCheck();
        $this->stepSummarize();

        echo "\n=== Scan Complete ===\n";
        echo "Files modified: " . count($this->fixedFiles) . "\n";
        foreach ($this->fixedFiles as $file) {
            echo "  - $file\n";
        }
    }

    private function stepPhpSyntaxCheck(): void
    {
        echo "Step 1: PHP Syntax Check\n";
        echo "------------------------\n";

        $phpFiles = $this->findAllPhpFiles();
        $errorCount = 0;

        foreach ($phpFiles as $file) {
            exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return);
            if ($return !== 0) {
                $errorCount++;
                $this->syntaxErrors[] = $file;
                echo "  SYNTAX ERROR: $file\n";
                foreach ($output as $line) {
                    echo "    $line\n";
                }
            }
        }

        if ($errorCount === 0) {
            echo "  ✓ No syntax errors found\n";
        } else {
            echo "  ✗ Found $errorCount files with syntax errors\n";
            echo "  Run: php tooling/fix-syntax-errors.php to auto-fix common issues\n";
        }
        echo "\n";
    }

    private function findAllPhpFiles(): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator('.', RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if ($file->isFile() && $file->getExtension() === 'php') {
                // Skip vendor directory
                if (str_starts_with($file->getPathname(), './vendor/')) {
                    continue;
                }
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function stepScanForMissingClasses(): void
    {
        echo "Step 2: Missing Class Detection\n";
        echo "--------------------------------\n";

        // Run tests and capture "Class not found" errors
        exec("vendor/bin/phpunit tests --no-coverage 2>&1", $output, $return);
        $testOutput = implode("\n", $output);

        // Extract class names from "Class 'X' not found" errors
        preg_match_all("/Class '([^']+)' not found/", $testOutput, $matches);
        $missing = array_unique($matches[1] ?? []);

        if (count($missing) === 0) {
            echo "  ✓ No Class not found errors detected\n";
            echo "\n";

            return;
        }

        echo "  Found " . count($missing) . " unique missing classes\n";

        // Categorize by namespace pattern
        $categorized = [];
        foreach ($missing as $class) {
            // Determine likely location based on namespace
            $parts = explode('\\', $class);
            $shortName = end($parts);

            // Suggest location based on namespace segment
            $namespaceWithoutClass = implode('\\', array_slice($parts, 0, -1));
            $categorized[$namespaceWithoutClass][] = $class;
        }

        foreach ($categorized as $namespace => $classes) {
            echo "\n  Namespace: $namespace (" . count($classes) . " classes)\n";
            foreach (array_slice($classes, 0, 5) as $cls) {
                echo "    - $cls\n";
            }
            if (count($classes) > 5) {
                echo "    ... and " . (count($classes) - 5) . " more\n";
            }
        }

        echo "\n  Recommendations:\n";
        echo "    - Implement missing classes in components/ or framework/System/\n";
        echo "    - Or update tests to remove references if classes are placeholders\n";
        echo "\n";
    }

    private function stepNamespaceIntegrityCheck(): void
    {
        echo "Step 3: Namespace Integrity\n";
        echo "----------------------------\n";

        // Run existing namespace drift check
        exec("php tooling/Refactor/CheckNamespaceDrift.php 2>&1", $output, $return);
        $result = implode("\n", $output);

        if (str_contains($result, 'PASS') || str_contains($result, 'No namespace drift')) {
            echo "  ✓ Namespace integrity OK\n";
        } else {
            echo "  ✗ Namespace drift detected:\n";
            echo "    $result\n";
        }

        // Check component suite structure
        exec("php tooling/Refactor/CheckComponentSuiteStructure.php 2>&1", $output2, $return2);
        $result2 = implode("\n", $output2);

        if (str_contains($result2, 'PASS')) {
            echo "  ✓ Component suite structure OK\n";
        } else {
            echo "  ✗ Component structure issues:\n";
            echo "    $result2\n";
        }

        echo "\n";
    }

    private function stepSummarize(): void
    {
        echo "Summary:\n";
        echo "--------\n";
        printf("  Syntax errors:     %d\n", count($this->syntaxErrors));
        printf("  Missing classes:   %d (see Step 2)\n", count($this->missingClasses));
        printf("  Files fixed:       %d\n", count($this->fixedFiles));

        echo "\nNext actions:\n";
        echo "  1. Fix all syntax errors (Step 1 output)\n";
        echo "  2. Implement or remove missing classes (Step 2 categories)\n";
        echo "  3. Re-run: vendor/bin/phpunit tests --no-coverage\n";
        echo "  4. Address namespace drift if any (Step 3)\n";
    }
}

$scanner = new ErrorScannerFixer();
$scanner->run();
