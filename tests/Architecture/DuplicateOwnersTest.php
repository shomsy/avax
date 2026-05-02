<?php

declare(strict_types=1);

namespace Avax\Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Checks for duplicate class/interface/trait/enum definitions across the codebase.
 *
 * Each class name should only be defined once to avoid ambiguity.
 */
final class DuplicateOwnersTest extends TestCase
{
    private string $projectRoot;

    #[Test]
    public function no_duplicate_class_definitions_in_framework(): void
    {
        $duplicates = $this->findDuplicatesInDirectory(
            directory: $this->projectRoot . '/framework',
        );

        $this->assertEmpty(
            actual : $duplicates,
            message: "Duplicate class definitions found in framework:\n" . $this->formatDuplicates($duplicates),
        );
    }

    /**
     * Find duplicate class/trait/enum definitions in a directory.
     *
     * @return array<string, list<string>>
     */
    private function findDuplicatesInDirectory(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $definitions = [];
        $iterator    = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        /** @var RecursiveDirectoryIterator $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }

            // Find class, trait, enum, or interface declarations
            $patterns = [
                '/^\s*(?:abstract\s+|final\s+)?class\s+(\w+)/m',
                '/^\s*(?:abstract\s+|final\s+)?trait\s+(\w+)/m',
                '/^\s*enum\s+(\w+)/m',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content, $matches)) {
                    $name                 = $matches[1];
                    $relativePath         = str_replace($this->projectRoot . '/', '', $file->getPathname());
                    $definitions[$name][] = $relativePath;
                }
            }
        }

        // Filter to only duplicates
        return array_filter($definitions, static fn (array $paths): bool => count($paths) > 1);
    }

    /**
     * Format duplicate definitions for error message.
     *
     * @param array<string, list<string>> $duplicates
     */
    private function formatDuplicates(array $duplicates): string
    {
        $output = '';
        foreach ($duplicates as $name => $paths) {
            $output .= "\n  {$name}:\n";
            foreach ($paths as $path) {
                $output .= "    - {$path}\n";
            }
        }

        return $output;
    }

    #[Test]
    public function no_duplicate_class_definitions_in_components(): void
    {
        $duplicates = $this->findDuplicatesInDirectory(
            directory: $this->projectRoot . '/components',
        );

        $this->assertEmpty(
            actual : $duplicates,
            message: "Duplicate class definitions found in components:\n" . $this->formatDuplicates($duplicates),
        );
    }

    #[Test]
    public function no_duplicate_interface_definitions(): void
    {
        $duplicates = $this->findInterfaceDuplicates();

        $this->assertEmpty(
            actual : $duplicates,
            message: "Duplicate interface definitions found:\n" . $this->formatDuplicates($duplicates),
        );
    }

    /**
     * Find duplicate interface definitions.
     *
     * @return array<string, list<string>>
     */
    private function findInterfaceDuplicates(): array
    {
        $directories = [
            $this->projectRoot . '/framework',
            $this->projectRoot . '/components',
        ];

        $definitions = [];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            );

            /** @var RecursiveDirectoryIterator $file */
            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $content = file_get_contents($file->getPathname());
                if ($content === false) {
                    continue;
                }

                if (preg_match('/^\s*interface\s+(\w+)/m', $content, $matches)) {
                    $name = $matches[1];
                    // Skip common test mock interfaces like Stringable
                    if (in_array($name, ['Stringable'], true)) {
                        continue;
                    }
                    $relativePath         = str_replace($this->projectRoot . '/', '', $file->getPathname());
                    $definitions[$name][] = $relativePath;
                }
            }
        }

        return array_filter($definitions, static fn (array $paths): bool => count($paths) > 1);
    }

    protected function setUp(): void
    {
        $this->projectRoot = dirname(__DIR__, 2);
    }
}
