<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\DataTransfer;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CastWith;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\CacheDataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\CompileDataShapeSchema;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\CompiledSchemaMetadata;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\InspectDataShape;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

final class CompileDataShapeSchemaTest extends TestCase
{
    private string $cacheDir;
    private Filesystem $filesystem;
    private InspectDataShape $inspector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheDir = sys_get_temp_dir() . '/avax-test-compile-' . uniqid();
        $this->filesystem = new Filesystem();
        $this->inspector = new InspectDataShape();
        CacheDataShape::reset();
    }

    protected function tearDown(): void
    {
        $this->cleanupDir($this->cacheDir);
        CacheDataShape::reset();
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
    public function compile_produces_valid_metadata_with_schema_version(): void
    {
        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'test-hash',
            filesystem: $this->filesystem,
        );

        $metadata = $compiler->compile(
            classes: [CompileTestDto::class],
            inspector: $this->inspector,
        );

        $this->assertSame(CompiledSchemaMetadata::FORMAT, $metadata->format);
        $this->assertSame(CompiledSchemaMetadata::SCHEMA_VERSION, $metadata->schemaVersion);
        $this->assertSame('test-hash', $metadata->configHash);
        $this->assertNotEmpty($metadata->fingerprint);
        $this->assertNotEmpty($metadata->checksum);
        $this->assertArrayHasKey(CompileTestDto::class, $metadata->entries);
    }

    #[Test]
    public function compile_includes_all_requested_classes(): void
    {
        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'test-hash',
            filesystem: $this->filesystem,
        );

        $metadata = $compiler->compile(
            classes: [CompileTestDto::class, CompileTestDtoTwo::class],
            inspector: $this->inspector,
        );

        $this->assertCount(2, $metadata->entries);
        $this->assertArrayHasKey(CompileTestDto::class, $metadata->entries);
        $this->assertArrayHasKey(CompileTestDtoTwo::class, $metadata->entries);
    }

    #[Test]
    public function compile_computes_fingerprint_from_config(): void
    {
        $compiler1 = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'hash-a',
            filesystem: $this->filesystem,
        );
        $compiler2 = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'hash-b',
            filesystem: $this->filesystem,
        );

        $metadata1 = $compiler1->compile([CompileTestDto::class], $this->inspector);
        $metadata2 = $compiler2->compile([CompileTestDto::class], $this->inspector);

        $this->assertNotSame($metadata1->fingerprint, $metadata2->fingerprint);
    }

    #[Test]
    public function compile_collects_source_mtimes(): void
    {
        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'test-hash',
            filesystem: $this->filesystem,
        );

        $metadata = $compiler->compile([CompileTestDto::class], $this->inspector);

        $this->assertArrayHasKey(CompileTestDto::class, $metadata->sourceMtimes);
        $this->assertIsInt($metadata->sourceMtimes[CompileTestDto::class]);
    }

    #[Test]
    public function atomic_write_uses_temp_then_rename(): void
    {
        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'test-hash',
            filesystem: $this->filesystem,
        );

        $compiler->compile([CompileTestDto::class], $this->inspector);

        $path = $this->cacheDir . '/compiled/schema.json';
        $this->assertFileExists($path);
        // No .tmp files should remain
        $tmpFiles = glob($this->cacheDir . '/compiled/schema.json.tmp.*');
        $this->assertEmpty($tmpFiles);
    }

    #[Test]
    public function load_metadata_returns_null_when_file_missing(): void
    {
        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'test-hash',
            filesystem: $this->filesystem,
        );

        $this->assertNull($compiler->loadMetadata());
    }

    #[Test]
    public function load_metadata_returns_null_when_json_invalid(): void
    {
        $dir = $this->cacheDir . '/compiled';
        mkdir($dir, 0o755, true);
        file_put_contents($dir . '/schema.json', 'not-valid-json{{{');

        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'test-hash',
            filesystem: $this->filesystem,
        );

        $this->assertNull($compiler->loadMetadata());
    }

    #[Test]
    public function load_metadata_returns_null_when_format_wrong(): void
    {
        $body = ['format' => 'wrong-format', 'schemaVersion' => 1, 'checksum' => 'abc'];
        $dir = $this->cacheDir . '/compiled';
        mkdir($dir, 0o755, true);
        file_put_contents($dir . '/schema.json', json_encode($body));

        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'test-hash',
            filesystem: $this->filesystem,
        );

        $this->assertNull($compiler->loadMetadata());
    }

    #[Test]
    public function load_metadata_returns_null_when_schema_version_mismatch(): void
    {
        $body = [
            'format' => CompiledSchemaMetadata::FORMAT,
            'schemaVersion' => 999,
            'compiledAt' => '',
            'configHash' => '',
            'fingerprint' => '',
            'entries' => [],
            'sourceMtimes' => [],
            'checksum' => 'dummy',
        ];
        $dir = $this->cacheDir . '/compiled';
        mkdir($dir, 0o755, true);
        file_put_contents($dir . '/schema.json', json_encode($body));

        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'test-hash',
            filesystem: $this->filesystem,
        );

        $this->assertNull($compiler->loadMetadata());
    }

    #[Test]
    public function load_metadata_returns_null_when_checksum_mismatch(): void
    {
        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'test-hash',
            filesystem: $this->filesystem,
        );

        // First compile valid metadata
        $compiler->compile([CompileTestDto::class], $this->inspector);

        // Then tamper with the file
        $path = $this->cacheDir . '/compiled/schema.json';
        $json = file_get_contents($path);
        self::assertIsString($json);
        $data = json_decode($json, true);
        $data['checksum'] = 'tampered-checksum';
        file_put_contents($path, json_encode($data));

        $this->assertNull($compiler->loadMetadata());
    }

    #[Test]
    public function load_metadata_quarantines_corrupt_file(): void
    {
        $dir = $this->cacheDir . '/compiled';
        mkdir($dir, 0o755, true);
        file_put_contents($dir . '/schema.json', 'not-valid-json');

        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'test-hash',
            filesystem: $this->filesystem,
        );

        $compiler->loadMetadata();

        $quarantineDir = $this->cacheDir . '/compiled/quarantine';
        $this->assertDirectoryExists($quarantineDir);
        $files = scandir($quarantineDir);
        // Should have . and .. plus the quarantined file
        $this->assertGreaterThan(2, count($files));
        // Original file should be gone
        $this->assertFileDoesNotExist($dir . '/schema.json');
    }

    #[Test]
    public function has_source_changed_detects_mtime_difference(): void
    {
        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'test-hash',
            filesystem: $this->filesystem,
        );

        $metadata = $compiler->compile([CompileTestDto::class], $this->inspector);

        // Touch the source file to change mtime
        $reflection = new ReflectionClass(CompileTestDto::class);
        $fileName = $reflection->getFileName();
        if ($fileName !== false) {
            touch($fileName, time() + 100);
            $this->assertTrue($compiler->hasSourceChanged(CompileTestDto::class, $metadata));
        }
    }

    #[Test]
    public function has_source_changed_returns_false_when_unchanged(): void
    {
        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'test-hash',
            filesystem: $this->filesystem,
        );

        $metadata = $compiler->compile([CompileTestDto::class], $this->inspector);

        $this->assertFalse($compiler->hasSourceChanged(CompileTestDto::class, $metadata));
    }

    #[Test]
    public function load_metadata_round_trip_valid(): void
    {
        $compiler = new CompileDataShapeSchema(
            cacheDir: $this->cacheDir,
            configHash: 'test-hash',
            filesystem: $this->filesystem,
        );

        $compiled = $compiler->compile([CompileTestDto::class], $this->inspector);
        $loaded = $compiler->loadMetadata();

        $this->assertNotNull($loaded);
        $this->assertSame($compiled->format, $loaded->format);
        $this->assertSame($compiled->schemaVersion, $loaded->schemaVersion);
        $this->assertSame($compiled->configHash, $loaded->configHash);
        $this->assertSame($compiled->fingerprint, $loaded->fingerprint);
        $this->assertArrayHasKey(CompileTestDto::class, $loaded->entries);
    }
}

// Test DTOs
final class CompileTestDto
{
    public function __construct(
        #[Required]
        #[StringType]
        public string $name,
        #[MapFrom('email_address')]
        public string $email,
    ) {}
}

final class CompileTestDtoTwo
{
    public function __construct(
        #[Required]
        public int $id,
    ) {}
}
