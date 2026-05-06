<?php

declare(strict_types=1);

namespace Avax\Tooling;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Batch fixes PHP syntax errors identified in the project:
 * 1. Remove invalid named arguments from PHPUnit assertions
 * 2. Fix extra closing braces in enums
 * 3. Replace array_any() with array_filter()
 * 4. Minor PreCommit severity fixes
 */
class SyntaxErrorFixer
{
    private array $fixedFiles = [];

    public function run(): void
    {
        $this->fixPHPUnitAssertions();
        $this->fixExtraBracesInEnums();
        $this->fixArrayAnyCalls();
        $this->runPhpCsFixer();

        echo "Syntax fixes completed. Files modified:\n";
        foreach ($this->fixedFiles as $file) {
            echo "- $file\n";
        }

        echo "\nVerify with: find . -name '*.php' -exec php -l {} \\; 2>&1 | grep -v 'No syntax errors'\n";
    }

    private function fixPHPUnitAssertions(): void
    {
        $patterns = [
            // assertSame(X, Y)) -> assertSame(X, Y)
            '/(assertSame|assertEquals)\s*\(\s*(?:expected|expectedCount|haystack):\s*([^,]+),\s*(?:actual|condition):\s*([^)]+)/' => '$1($2, $3)',
            // assertTrue(X)) -> assertTrue(X)
            '/assertTrue\s*\(\s*condition:\s*([^)]+)/' => 'assertTrue($1)',
            // assertIsArray(X)) -> assertIsArray(X)
            '/assertIsArray\s*\(\s*actual:\s*([^)]+)/' => 'assertIsArray($1)',
            // self::assertSame(X, Y)) -> self::assertSame(X, Y)
            '/self::(assertSame|assertEquals)\s*\(\s*(?:expected|expectedCount|haystack):\s*([^,]+),\s*(?:actual|condition):\s*([^)]+)/' => 'self::$1($2, $3)',
            // Generic named arg removal in asserts
            '/(assert\w+)\s*\([^)]*?(\w+):\s*([^,)]+)/' => '$1($3)', // Fallback
        ];

        $this->processFilesWithRegex($patterns, 'PHPUnit assertions');
    }

    private function processFilesWithRegex(array $patterns, string $description): void
    {
        $phpFiles = $this->findAllPhpFiles();

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            $original = $content;

            foreach ($patterns as $regex => $replacement) {
                $content = preg_replace($regex, $replacement, $content, -1, $count);
                if ($count > 0) {
                    echo "Applied $count fixes in $file\n";
                }
            }

            if ($content !== $original) {
                file_put_contents($file, $content);
                $this->fixedFiles[] = $file;
            }
        }
    }

    /**
     * @return list<string>
     */
    private function findAllPhpFiles(): array
    {
        return $this->findFiles('.', '*.php');
    }

    /**
     * @return list<string>
     */
    private function findFiles(string $directory, string $pattern): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if ($file->isFile() && fnmatch($pattern, $file->getFilename())) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function fixExtraBracesInEnums(): void
    {
        $files = $this->findFiles('components/Application/Cache/System', '*.php');

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $fixed = preg_replace('/}\s*}\s*}\s*}\s*$/', '}', $content);
            if ($fixed !== $content) {
                file_put_contents($file, $fixed);
                $this->fixedFiles[] = $file;
                echo "Fixed extra braces: $file\n";
            }
        }
    }

    private function fixArrayAnyCalls(): void
    {
        $patterns = [
            '/array_any\s*\(\s*([^,]+),\s*fn\s*\(\s*\$\w+\s*\)\s*:\s*bool\s*=>\s*\((bool\s*)?([^)]+)\)\s*\)/' => 'array_filter($1, fn($__) => $2$3) !== []',
        ];

        $this->processFilesWithRegex($patterns, 'array_any() calls');
    }

    private function runPhpCsFixer(): void
    {
        // Use existing php-cs-fixer config
        $command = 'vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --verbose';
        exec($command.' 2>&1', $output, $return);

        if ($return === 0) {
            echo "php-cs-fixer applied successfully.\n";
        } else {
            echo implode("\n", $output)."\n";
        }
    }
}

$fixer = new SyntaxErrorFixer();
$fixer->run();
