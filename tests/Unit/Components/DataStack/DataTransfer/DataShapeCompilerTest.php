<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\DataTransfer;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\CacheDataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\CompileDataShapeSchema;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataShapeCompiler;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\InspectDataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\ReadClassDataShape;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

final class DataShapeCompilerTest extends TestCase
{
    private string $cacheDir;
    private InspectDataShape $inspector;
    private DataTransferConfig $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheDir = sys_get_temp_dir() . '/avax-test-compiler-' . uniqid();
        $this->config = DataTransferConfig::default();
        $this->inspector = new InspectDataShape(dataTransferConfig: $this->config);
        CacheDataShape::reset();
        DataShapeCompiler::reset();
    }

    protected function tearDown(): void
    {
        $this->cleanupDir($this->cacheDir);
        CacheDataShape::reset();
        DataShapeCompiler::reset();
        parent::tearDown();
    }

    private function cleanupDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }

    #[Test]
    public function resolve_returns_data_shape(): void
    {
        $compiler = new DataShapeCompiler(
            inspectDataShape: $this->inspector,
            readClassDataShape: new ReadClassDataShape(),
        );

        $shape = $compiler->resolve(CompilerTestDto::class, $this->config);

        $this->assertInstanceOf(DataShape::class, $shape);
        $this->assertSame(CompilerTestDto::class, $shape->class);
    }

    #[Test]
    public function resolve_falls_back_to_inspect_data_shape_when_no_cache(): void
    {
        $compiler = new DataShapeCompiler(
            inspectDataShape: $this->inspector,
            readClassDataShape: new ReadClassDataShape(),
            // No CompileDataShapeSchema — should fall back to InspectDataShape
        );

        $shape = $compiler->resolve(CompilerTestDto::class, $this->config);

        $this->assertInstanceOf(DataShape::class, $shape);
        $this->assertArrayHasKey('name', $shape->fields());
    }

    #[Test]
    public function resolve_falls_back_when_compiled_corrupt(): void
    {
        // Write corrupt metadata
        $dir = $this->cacheDir . '/compiled';
        mkdir($dir, 0o755, true);
        file_put_contents($dir . '/schema.json', 'not-valid-json');

        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: (string) spl_object_id($this->config),
            filesystem: new Filesystem(),
        );

        $shapeCompiler = new DataShapeCompiler(
            inspectDataShape: $this->inspector,
            readClassDataShape: new ReadClassDataShape(),
            compileDataShapeSchema: $compiler,
            configHash: (string) spl_object_id($this->config),
        );

        // Should fall back to InspectDataShape since metadata is corrupt
        $shape = $shapeCompiler->resolve(CompilerTestDto::class, $this->config);
        $this->assertInstanceOf(DataShape::class, $shape);
    }

    #[Test]
    public function resolve_uses_compiled_metadata_when_valid(): void
    {
        $configHash = (string) spl_object_id($this->config);

        // First compile the metadata
        $diskCompiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: $configHash,
            filesystem: new Filesystem(),
        );
        $diskCompiler->compile([CompilerTestDto::class], $this->inspector);

        // Now create a compiler that uses the compiled metadata
        $shapeCompiler = new DataShapeCompiler(
            inspectDataShape: $this->inspector,
            readClassDataShape: new ReadClassDataShape(),
            compileDataShapeSchema: $diskCompiler,
            configHash: $configHash,
        );

        $shape = $shapeCompiler->resolve(CompilerTestDto::class, $this->config);

        $this->assertInstanceOf(DataShape::class, $shape);
        $this->assertArrayHasKey('name', $shape->fields());
        $this->assertArrayHasKey('email', $shape->fields());
    }

    #[Test]
    public function resolve_invalidates_when_source_changed(): void
    {
        $configHash = (string) spl_object_id($this->config);

        // Compile the metadata
        $diskCompiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: $configHash,
            filesystem: new Filesystem(),
        );
        $metadata = $diskCompiler->compile([CompilerTestDto::class], $this->inspector);

        // Touch the source file
        $reflection = new ReflectionClass(CompilerTestDto::class);
        $fileName = $reflection->getFileName();
        if ($fileName !== false) {
            touch($fileName, time() + 100);
        }

        $shapeCompiler = new DataShapeCompiler(
            inspectDataShape: $this->inspector,
            readClassDataShape: new ReadClassDataShape(),
            compileDataShapeSchema: $diskCompiler,
            configHash: $configHash,
        );

        // Should fall back since source changed
        $shape = $shapeCompiler->resolve(CompilerTestDto::class, $this->config);
        $this->assertInstanceOf(DataShape::class, $shape);
    }

    #[Test]
    public function resolve_caches_result(): void
    {
        $compiler = new DataShapeCompiler(
            inspectDataShape: $this->inspector,
            readClassDataShape: new ReadClassDataShape(),
        );

        $shape1 = $compiler->resolve(CompilerTestDto::class, $this->config);
        $shape2 = $compiler->resolve(CompilerTestDto::class, $this->config);

        $this->assertSame($shape1, $shape2);
    }

    #[Test]
    public function reset_clears_static_cache(): void
    {
        $compiler = new DataShapeCompiler(
            inspectDataShape: $this->inspector,
            readClassDataShape: new ReadClassDataShape(),
        );

        $shape1 = $compiler->resolve(CompilerTestDto::class, $this->config);
        DataShapeCompiler::reset();
        $shape2 = $compiler->resolve(CompilerTestDto::class, $this->config);

        // After reset, a new shape is created (not the same instance)
        $this->assertNotSame($shape1, $shape2);
        // But same class name
        $this->assertSame($shape1->class, $shape2->class);
    }
}

// Test DTOs
final class CompilerTestDto
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}
}
