<?php

declare(strict_types=1);

namespace Avax\Tests\E2E;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Column;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Entity;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Id;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Table;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CompileClassAttributes;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CompiledAttributeMetadata;
use Avax\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * E2ECompiledMetadataTest — proves compiled metadata works end-to-end.
 *
 * V5-22: Compiles real class attributes to disk, then loads them back,
 * proving the compile → store → load → use pipeline works.
 */
final class E2ECompiledMetadataTest extends TestCase
{
    private string $cacheDir;

    #[Test]
    public function compileAndLoadEntityMetadata() : void
    {
        $compiler = new CompileClassAttributes($this->cacheDir, 'e2e-test', new Filesystem());

        // Compile TestEntityWithAttributes metadata
        $metadata = $compiler->compile(TestEntityWithAttributes::class);

        self::assertSame(TestEntityWithAttributes::class, $metadata->className);
        self::assertArrayHasKey('Entity', $metadata->classAttributes);

        // Load it back from disk
        $loaded = $compiler->loadMetadata(TestEntityWithAttributes::class);
        self::assertNotNull($loaded);
        self::assertSame($metadata->className, $loaded->className);
        self::assertArrayHasKey('Entity', $loaded->classAttributes);
        self::assertArrayHasKey('Id', $loaded->propertyAttributes['id']);
        self::assertArrayHasKey('Column', $loaded->propertyAttributes['name']);
    }

    #[Test]
    public function compiledMetadataSurvivesCacheDirectoryRecreation() : void
    {
        $fs        = new Filesystem();
        $compiler1 = new CompileClassAttributes($this->cacheDir, 'e2e-test', $fs);
        $compiler1->compile(TestEntityWithAttributes::class);

        // Verify file exists
        $safeName  = str_replace('\\', '_', TestEntityWithAttributes::class);
        $cacheFile = $this->cacheDir . '/compiled-attributes/attributes.' . $safeName . '.json';
        self::assertFileExists($cacheFile);

        // Load with a fresh compiler instance
        $compiler2 = new CompileClassAttributes($this->cacheDir, 'e2e-test', $fs);
        $loaded    = $compiler2->loadMetadata(TestEntityWithAttributes::class);

        self::assertNotNull($loaded);
        self::assertArrayHasKey('Entity', $loaded->classAttributes);
    }

    #[Test]
    public function compiledMetadataInvalidatedOnConfigChange() : void
    {
        $fs        = new Filesystem();
        $compiler1 = new CompileClassAttributes($this->cacheDir, 'original-config', $fs);
        $compiler1->compile(TestEntityWithAttributes::class);

        // Load with different config hash — should be invalid
        $compiler2 = new CompileClassAttributes($this->cacheDir, 'different-config', $fs);
        $loaded    = $compiler2->loadMetadata(TestEntityWithAttributes::class);

        self::assertNull($loaded);
    }

    protected function setUp() : void
    {
        parent::setUp();
        $this->cacheDir = sys_get_temp_dir() . '/avax-e2e-metadata-' . uniqid();
    }

    protected function tearDown() : void
    {
        if (is_dir($this->cacheDir)) {
            $this->recursiveDelete($this->cacheDir);
        }
        parent::tearDown();
    }

    private function recursiveDelete(string $dir) : void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->recursiveDelete($path) : unlink($path);
        }
        rmdir($dir);
    }
}

// Test fixture — a real entity class with ORM attributes

#[Entity]
#[Table(name: 'test_entities')]
final class TestEntityWithAttributes
{
    #[Id]
    #[Column(type: 'int')]
    public int $id;

    #[Column(type: 'string', nullable: false)]
    public string $name;

    public function doSomething() : void {}
}
