<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Architecture tests for DataStack/Data.
 *
 * These tests enforce the canonical AvaX architecture:
 * - Forms vs Structures are separate layers
 * - Arrhae is the independent engine (zero Collection/Json/Structure imports)
 * - Collection and Json compose Arrhae
 * - No placeholder or unimplemented structures
 * - No Data-prefixed public structure names
 */
final class ArchitectureTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        $this->basePath = __DIR__ . '/../../../../../components/DataStack/Data/System/Capabilities';
    }

    /** @return string */
    private function readCapabilityFile(string $relativePath): string
    {
        $content = file_get_contents($this->basePath . '/' . $relativePath);
        self::assertIsString($content, "File {$relativePath} must be readable.");

        return $content;
    }

    // ── Layer isolation ─────────────────────────────────────────────────

    public function test_arrhae_has_zero_collection_imports(): void
    {
        $file = $this->readCapabilityFile('Forms/ArrayForm/Arrhae.php');
        self::assertStringNotContainsString('CollectionForm', $file, 'Arrhae must not import Collection.');
    }

    public function test_arrhae_has_zero_json_imports(): void
    {
        $file = $this->readCapabilityFile('Forms/ArrayForm/Arrhae.php');
        self::assertStringNotContainsString('JsonForm', $file, 'Arrhae must not import Json.');
    }

    public function test_arrhae_has_zero_data_structure_imports(): void
    {
        $file = $this->readCapabilityFile('Forms/ArrayForm/Arrhae.php');
        // Arrhae may import Structures/Functional (Pair, etc.) — those are composites, not invariant-bearing structures.
        // Arrhae must NOT import Linear, Maps, or Sets — those are real data structures.
        self::assertStringNotContainsString('Structures\\Linear', $file, 'Arrhae must not import Linear structures.');
        self::assertStringNotContainsString('Structures\\Maps', $file, 'Arrhae must not import Map structures.');
        self::assertStringNotContainsString('Structures\\Sets', $file, 'Arrhae must not import Set structures.');
    }

    public function test_collection_depends_on_arrhae(): void
    {
        $file = $this->readCapabilityFile('Forms/CollectionForm/Collection.php');
        self::assertStringContainsString('ArrayForm\\Arrhae', $file, 'Collection must import Arrhae.');
    }

    public function test_json_depends_on_arrhae(): void
    {
        $file = $this->readCapabilityFile('Forms/JsonForm/Json.php');
        self::assertStringContainsString('ArrayForm\\Arrhae', $file, 'Json must import Arrhae.');
    }

    // ── Canonical shape ─────────────────────────────────────────────────

    public function test_no_collection_internal_directory(): void
    {
        $path = $this->basePath . '/Forms/CollectionForm/Internal';
        self::assertDirectoryDoesNotExist($path, 'Collection must not have Internal directory.');
    }

    public function test_no_capabilities_foundation_duplicate(): void
    {
        self::assertDirectoryDoesNotExist($this->basePath . '/Foundation/Foundation', 'No duplicate Foundation directory.');
    }

    // ── No placeholder structures ───────────────────────────────────────

    /**
     * @dataProvider placeholderStructureProvider
     */
    public function test_no_placeholder_structures(string $structureName): void
    {
        $directories = [
            'Structures/Linear',
            'Structures/Maps',
            'Structures/Sets',
            'Structures/Functional',
        ];

        foreach ($directories as $dir) {
            $path = $this->basePath . '/' . $dir . '/' . $structureName;
            self::assertDirectoryDoesNotExist($path, "Placeholder {$structureName} must not exist.");
        }
    }

    /** @return array<string, array{string}> */
    public static function placeholderStructureProvider(): array
    {
        return [
            'Queue' => ['Queue'],
            'Stack' => ['Stack'],
            'Deque' => ['Deque'],
            'PriorityQueue' => ['PriorityQueue'],
        ];
    }

    // ── No Data-prefixed public structure names ─────────────────────────

    public function test_no_data_prefixed_public_structure_names(): void
    {
        $structureDirs = [
            'Structures/Linear',
            'Structures/Maps',
            'Structures/Sets',
        ];

        foreach ($structureDirs as $dir) {
            $fullPath = $this->basePath . '/' . $dir;
            if (!is_dir($fullPath)) {
                continue;
            }

            $files = scandir($fullPath);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || !str_ends_with($file, '.php')) {
                    continue;
                }

                // DataList is a known legacy file pending merge into Sequence.
                if ($file === 'DataList.php') {
                    continue;
                }

                self::assertFalse((bool) preg_match('/^Data/', $file), "Structure file {$dir}/{$file} must not have Data prefix.");
            }
        }
    }

    // ── PublicSurface facades ───────────────────────────────────────────

    public function test_public_surface_has_form_facades(): void
    {
        $ps = $this->basePath . '/../PublicSurface';
        self::assertFileExists($ps . '/Arrhae.php');
        self::assertFileExists($ps . '/Collection.php');
        self::assertFileExists($ps . '/Json.php');
    }

    public function test_public_surface_has_structure_facades(): void
    {
        $ps = $this->basePath . '/../PublicSurface';
        self::assertFileExists($ps . '/Map.php');
        self::assertFileExists($ps . '/Set.php');
        self::assertFileExists($ps . '/Sequence.php');
        self::assertFileExists($ps . '/OrderedMap.php');
        self::assertFileExists($ps . '/OrderedSet.php');
        self::assertFileExists($ps . '/MultiMap.php');
    }

    public function test_public_surface_has_no_unimplemented_facades(): void
    {
        $ps = $this->basePath . '/../PublicSurface';
        self::assertFileDoesNotExist($ps . '/Queue.php');
        self::assertFileDoesNotExist($ps . '/Stack.php');
        self::assertFileDoesNotExist($ps . '/Deque.php');
        self::assertFileDoesNotExist($ps . '/PriorityQueue.php');
    }

    // ── Semantic folder naming ──────────────────────────────────────────

    public function test_no_forbidden_folder_names(): void
    {
        $forbidden = ['Services', 'Helpers', 'Utils', 'Common', 'Shared', 'Managers', 'Core', 'Support'];

        $iterator = new RecursiveDirectoryIterator($this->basePath, RecursiveDirectoryIterator::SKIP_DOTS);
        $checked = 0;
        foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST) as $file) {
            if ($file->isDir()) {
                ++$checked;
                self::assertNotContains($file->getFilename(), $forbidden, 'Forbidden folder name: ' . $file->getPathname());
            }
        }

        self::assertGreaterThan(0, $checked, 'Should have checked at least one directory');
    }
}
