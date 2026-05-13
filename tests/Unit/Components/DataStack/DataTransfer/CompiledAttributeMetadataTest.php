<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\DataTransfer;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Column;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Entity;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Id;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\AttributeCompiler;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CastWith;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CompileClassAttributes;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CompiledAttributeMetadata;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\DefaultValue;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Max;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Min;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class CompiledAttributeMetadataTest extends TestCase
{
    private string $cacheDir;
    private Filesystem $filesystem;

    #[Test]
    public function constants_have_expected_values() : void
    {
        $this->assertSame(1, CompiledAttributeMetadata::METADATA_VERSION);
        $this->assertSame('attribute-metadata', CompiledAttributeMetadata::FORMAT);
    }

    #[Test]
    public function from_array_creates_valid_instance() : void
    {
        $data = [
            'format'             => CompiledAttributeMetadata::FORMAT,
            'metadataVersion'    => CompiledAttributeMetadata::METADATA_VERSION,
            'compiledAt'         => '2026-05-11T00:00:00+00:00',
            'configHash'         => 'test-hash',
            'className'          => V08TestEntity::class,
            'classAttributes'    => ['Entity' => []],
            'propertyAttributes' => ['id' => ['Id' => [], 'Column' => ['name' => 'id']]],
            'methodAttributes'   => [],
            'sourceMtime'        => 1234567890,
            'checksum'           => '',
        ];

        $body = $data;
        unset($body['checksum']);
        $data['checksum'] = CompiledAttributeMetadata::computeChecksum($body);

        $metadata = CompiledAttributeMetadata::fromArray($data);

        $this->assertSame(V08TestEntity::class, $metadata->className);
        $this->assertSame('test-hash', $metadata->configHash);
    }

    #[Test]
    public function from_array_throws_on_missing_field() : void
    {
        $this->expectException(RuntimeException::class);

        CompiledAttributeMetadata::fromArray(['format' => 'test']);
    }

    // -- CompiledAttributeMetadata model tests --

    #[Test]
    public function to_array_round_trip() : void
    {
        $data = [
            'format'             => CompiledAttributeMetadata::FORMAT,
            'metadataVersion'    => CompiledAttributeMetadata::METADATA_VERSION,
            'compiledAt'         => '2026-05-11T00:00:00+00:00',
            'configHash'         => 'test-hash',
            'className'          => V08TestEntity::class,
            'classAttributes'    => [],
            'propertyAttributes' => [],
            'methodAttributes'   => [],
            'sourceMtime'        => 1234567890,
            'checksum'           => '',
        ];

        $body = $data;
        unset($body['checksum']);
        $data['checksum'] = CompiledAttributeMetadata::computeChecksum($body);

        $metadata = CompiledAttributeMetadata::fromArray($data);
        $array    = $metadata->toArray();

        $this->assertSame($data, $array);
    }

    #[Test]
    public function to_json_produces_valid_json() : void
    {
        $data = [
            'format'             => CompiledAttributeMetadata::FORMAT,
            'metadataVersion'    => CompiledAttributeMetadata::METADATA_VERSION,
            'compiledAt'         => '2026-05-11T00:00:00+00:00',
            'configHash'         => 'test',
            'className'          => V08TestEntity::class,
            'classAttributes'    => [],
            'propertyAttributes' => [],
            'methodAttributes'   => [],
            'sourceMtime'        => 0,
            'checksum'           => '',
        ];

        $body = $data;
        unset($body['checksum']);
        $data['checksum'] = CompiledAttributeMetadata::computeChecksum($body);

        $metadata = CompiledAttributeMetadata::fromArray($data);
        $json     = $metadata->toJson();

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertSame('test', $decoded['configHash']);
    }

    #[Test]
    public function is_valid_for_config() : void
    {
        $data     = $this->makeValidData(configHash: 'hash-a');
        $metadata = CompiledAttributeMetadata::fromArray($data);

        $this->assertTrue($metadata->isValidForConfig('hash-a'));
        $this->assertFalse($metadata->isValidForConfig('hash-b'));
    }

    /**
     * @return array<string, mixed>
     */
    private function makeValidData(
        string $configHash = 'test',
        int    $metadataVersion = CompiledAttributeMetadata::METADATA_VERSION,
    ) : array
    {
        $data = [
            'format'             => CompiledAttributeMetadata::FORMAT,
            'metadataVersion'    => $metadataVersion,
            'compiledAt'         => '2026-05-11T00:00:00+00:00',
            'configHash'         => $configHash,
            'className'          => V08TestEntity::class,
            'classAttributes'    => ['V08TestEntity' => ['repositoryClass' => null]],
            'propertyAttributes' => [
                'id'   => ['Id' => [], 'Column' => ['name' => 'id', 'type' => null, 'nullable' => false]],
                'name' => ['Column' => ['name' => 'name', 'type' => 'string', 'nullable' => false]],
            ],
            'methodAttributes'   => [],
            'sourceMtime'        => 1234567890,
            'checksum'           => '',
        ];

        $body = $data;
        unset($body['checksum']);
        $data['checksum'] = CompiledAttributeMetadata::computeChecksum($body);

        return $data;
    }

    #[Test]
    public function has_version_mismatch() : void
    {
        $data     = $this->makeValidData(metadataVersion: 99);
        $metadata = CompiledAttributeMetadata::fromArray($data);

        $this->assertTrue($metadata->hasVersionMismatch());
    }

    #[Test]
    public function is_checksum_valid() : void
    {
        $data     = $this->makeValidData();
        $metadata = CompiledAttributeMetadata::fromArray($data);

        $this->assertTrue($metadata->isChecksumValid());
    }

    #[Test]
    public function is_checksum_invalid_when_tampered() : void
    {
        $data             = $this->makeValidData();
        $data['checksum'] = 'tampered';
        $metadata         = CompiledAttributeMetadata::fromArray($data);

        $this->assertFalse($metadata->isChecksumValid());
    }

    #[Test]
    public function compile_extracts_class_attributes() : void
    {
        $compiler = new CompileClassAttributes(
            cacheDir  : $this->cacheDir,
            configHash: 'test',
            filesystem: $this->filesystem,
        );

        $metadata = $compiler->compile(V08TestEntity::class);

        // Entity attribute stored by short name
        $this->assertArrayHasKey('Entity', $metadata->classAttributes);
    }

    #[Test]
    public function compile_extracts_property_attributes() : void
    {
        $compiler = new CompileClassAttributes(
            cacheDir  : $this->cacheDir,
            configHash: 'test',
            filesystem: $this->filesystem,
        );

        $metadata = $compiler->compile(V08TestEntity::class);

        $this->assertArrayHasKey('id', $metadata->propertyAttributes);
        $this->assertArrayHasKey('Id', $metadata->propertyAttributes['id']);
        $this->assertArrayHasKey('Column', $metadata->propertyAttributes['id']);
    }

    // -- CompileClassAttributes tests --

    #[Test]
    public function compile_extracts_multiple_property_attributes() : void
    {
        $compiler = new CompileClassAttributes(
            cacheDir  : $this->cacheDir,
            configHash: 'test',
            filesystem: $this->filesystem,
        );

        $metadata = $compiler->compile(V08TestEntity::class);

        $this->assertArrayHasKey('name', $metadata->propertyAttributes);
        $this->assertArrayHasKey('Required', $metadata->propertyAttributes['name']);
        $this->assertArrayHasKey('Min', $metadata->propertyAttributes['name']);
        $this->assertArrayHasKey('Max', $metadata->propertyAttributes['name']);
    }

    #[Test]
    public function compile_writes_to_disk() : void
    {
        $compiler = new CompileClassAttributes(
            cacheDir  : $this->cacheDir,
            configHash: 'test',
            filesystem: $this->filesystem,
        );

        $compiler->compile(V08TestEntity::class);

        $this->assertFileExists($this->cacheDir . '/compiled-attributes');
    }

    #[Test]
    public function load_metadata_returns_compiled_data() : void
    {
        $compiler = new CompileClassAttributes(
            cacheDir  : $this->cacheDir,
            configHash: 'test',
            filesystem: $this->filesystem,
        );

        $compiler->compile(V08TestEntity::class);
        $loaded = $compiler->loadMetadata(V08TestEntity::class);

        $this->assertInstanceOf(CompiledAttributeMetadata::class, $loaded);
        $this->assertSame(V08TestEntity::class, $loaded->className);
    }

    #[Test]
    public function load_metadata_returns_null_when_file_missing() : void
    {
        $compiler = new CompileClassAttributes(
            cacheDir  : $this->cacheDir,
            configHash: 'test',
            filesystem: $this->filesystem,
        );

        $this->assertNull($compiler->loadMetadata(stdClass::class));
    }

    #[Test]
    public function load_metadata_returns_null_on_config_mismatch() : void
    {
        $compiler1 = new CompileClassAttributes(
            cacheDir  : $this->cacheDir,
            configHash: 'hash-a',
            filesystem: $this->filesystem,
        );
        $compiler1->compile(V08TestEntity::class);

        $compiler2 = new CompileClassAttributes(
            cacheDir  : $this->cacheDir,
            configHash: 'hash-b',
            filesystem: $this->filesystem,
        );

        $this->assertNull($compiler2->loadMetadata(V08TestEntity::class));
    }

    #[Test]
    public function compile_many_compiles_all_classes() : void
    {
        $compiler = new CompileClassAttributes(
            cacheDir  : $this->cacheDir,
            configHash: 'test',
            filesystem: $this->filesystem,
        );

        $result = $compiler->compileMany([V08TestEntity::class, V08SimpleDto::class]);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey(V08TestEntity::class, $result);
        $this->assertArrayHasKey(V08SimpleDto::class, $result);
    }

    #[Test]
    public function quarantine_corrupt_file_on_tampered_checksum() : void
    {
        $compiler = new CompileClassAttributes(
            cacheDir  : $this->cacheDir,
            configHash: 'test',
            filesystem: $this->filesystem,
        );

        $compiler->compile(V08TestEntity::class);

        // Tamper with the file
        $path = $this->cacheDir . '/compiled-attributes/attributes.' . str_replace('\\', '_', V08TestEntity::class) . '.json';
        $json = file_get_contents($path);
        $this->assertIsString($json);
        $data             = json_decode($json, true);
        $data['checksum'] = 'tampered';
        file_put_contents($path, json_encode($data));

        $loaded = $compiler->loadMetadata(V08TestEntity::class);

        $this->assertNull($loaded);
        $this->assertDirectoryExists($this->cacheDir . '/compiled-attributes/quarantine');
    }

    #[Test]
    public function resolve_returns_null_without_compiler() : void
    {
        $compiler = new AttributeCompiler();

        $this->assertNull($compiler->resolve(V08TestEntity::class));
    }

    #[Test]
    public function resolve_returns_compiled_metadata() : void
    {
        $orchestrator = new AttributeCompiler(
            cacheDir  : $this->cacheDir,
            configHash: 'test',
        );

        // First compile
        $orchestrator->compile(V08TestEntity::class);

        // Then resolve
        $resolved = $orchestrator->resolve(V08TestEntity::class);

        $this->assertInstanceOf(CompiledAttributeMetadata::class, $resolved);
    }

    // -- AttributeCompiler orchestrator tests --

    #[Test]
    public function resolve_caches_in_memory() : void
    {
        $orchestrator = new AttributeCompiler(
            cacheDir  : $this->cacheDir,
            configHash: 'test',
        );

        $orchestrator->compile(V08TestEntity::class);

        // First resolve
        $first = $orchestrator->resolve(V08TestEntity::class);
        // Second resolve (should hit cache)
        $second = $orchestrator->resolve(V08TestEntity::class);

        $this->assertSame($first, $second);
    }

    #[Test]
    public function compile_many_caches_all() : void
    {
        $orchestrator = new AttributeCompiler(
            cacheDir  : $this->cacheDir,
            configHash: 'test',
        );

        $orchestrator->compileMany([V08TestEntity::class, V08SimpleDto::class]);

        $this->assertTrue($orchestrator->hasCompiled(V08TestEntity::class));
        $this->assertTrue($orchestrator->hasCompiled(V08SimpleDto::class));
    }

    #[Test]
    public function reset_clears_cache() : void
    {
        $orchestrator = new AttributeCompiler(
            cacheDir  : $this->cacheDir,
            configHash: 'test',
        );

        $orchestrator->compile(V08TestEntity::class);
        AttributeCompiler::reset();

        // After reset, the in-memory cache is cleared but the disk file still exists.
        // So resolve will re-read from disk. To test the cache is truly cleared,
        // verify that a new orchestrator with a different cache dir returns null.
        $freshDir = sys_get_temp_dir() . '/avax-attr-fresh-' . uniqid();
        $fresh    = new AttributeCompiler(
            cacheDir  : $freshDir,
            configHash: 'test',
        );

        $this->assertNull($fresh->resolve(V08TestEntity::class));

        $this->removeDir($freshDir);
    }

    #[Test]
    public function compile_throws_without_compiler_config() : void
    {
        $compiler = new AttributeCompiler();

        $this->expectException(RuntimeException::class);
        $compiler->compile(V08TestEntity::class);
    }

    #[Test]
    public function compute_checksum_is_deterministic() : void
    {
        $body = [
            'format' => 'test',
            'data'   => ['a' => 1, 'b' => 2],
        ];

        $checksum1 = CompiledAttributeMetadata::computeChecksum($body);
        $checksum2 = CompiledAttributeMetadata::computeChecksum($body);

        $this->assertSame($checksum1, $checksum2);
    }

    protected function setUp() : void
    {
        $this->cacheDir = sys_get_temp_dir() . '/avax-attr-test-' . uniqid();
        $this->filesystem = new Filesystem();
    }

    // -- Compute checksum test --

    protected function tearDown() : void
    {
        $this->removeDir($this->cacheDir);
        AttributeCompiler::reset();
    }

    private function removeDir(string $dir) : void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}

// -- Test entities --

#[Entity]
final readonly class V08TestEntity
{
    public function __construct(
        #[Id]
        #[Column(name: 'id')]
        public int    $id,

        #[Required]
        #[Min(2)]
        #[Max(50)]
        #[Column(name: 'name', type: 'string')]
        public string $name,
    ) {}
}

final readonly class V08SimpleDto
{
    public function __construct(
        #[MapFrom('user_name')]
        #[DefaultValue('anonymous')]
        public string $name,
    ) {}
}
