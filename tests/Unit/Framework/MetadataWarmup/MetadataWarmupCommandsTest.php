<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\MetadataWarmup;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Column;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Entity;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Id;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Table;
use Avax\Framework\System\Capabilities\MetadataWarmup\RegisterMetadataWarmCommands;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MetadataWarmupCommandsTest extends TestCase
{
    private string $cacheDir;

    #[Test]
    public function warmCommandCompilesEntityClasses() : void
    {
        $commands = new RegisterMetadataWarmCommands(
            cacheDir  : $this->cacheDir,
            configHash: 'test-hash',
            classes   : [TestEntity::class],
        );

        $result = ($commands()['metadata:warm'])([]);

        self::assertStringContainsString('Metadata Warm', $result);
        self::assertStringContainsString('✓', $result);
        self::assertStringContainsString('Compiled: 1', $result);
        self::assertStringContainsString('Errors: 0', $result);

        // Verify the compiled file exists on disk
        $compiledFile = $this->cacheDir . '/compiled-attributes/attributes.' . str_replace('\\', '_', TestEntity::class) . '.json';
        self::assertFileExists($compiledFile);
    }

    #[Test]
    public function warmCommandReportsErrorsForInvalidClasses() : void
    {
        /** @var list<class-string> $invalidClasses */
        $invalidClasses = ['NonExistentClass'];

        $commands = new RegisterMetadataWarmCommands(
            cacheDir  : $this->cacheDir,
            configHash: 'test-hash',
            classes   : $invalidClasses,
        );

        $result = ($commands()['metadata:warm'])([]);

        self::assertStringContainsString('Errors: 1', $result);
        self::assertStringContainsString('NonExistentClass', $result);
    }

    #[Test]
    public function warmCommandHandlesEmptyClassList() : void
    {
        $commands = new RegisterMetadataWarmCommands(
            cacheDir  : $this->cacheDir,
            configHash: 'test-hash',
            classes   : [],
        );

        $result = ($commands()['metadata:warm'])([]);

        self::assertStringContainsString('No classes configured', $result);
    }

    #[Test]
    public function clearCommandRemovesCompiledFiles() : void
    {
        // First warm up
        $commands = new RegisterMetadataWarmCommands(
            cacheDir  : $this->cacheDir,
            configHash: 'test-hash',
            classes   : [TestEntity::class],
        );
        ($commands()['metadata:warm'])([]);

        // Verify files exist
        $compiledFile = $this->cacheDir . '/compiled-attributes/attributes.' . str_replace('\\', '_', TestEntity::class) . '.json';
        self::assertFileExists($compiledFile);

        // Now clear
        $result = ($commands()['metadata:clear'])([]);

        self::assertStringContainsString('Metadata Clear', $result);
        self::assertStringContainsString('Removed 1', $result);
        self::assertFileDoesNotExist($compiledFile);
    }

    #[Test]
    public function clearCommandReportsNoCacheWhenEmpty() : void
    {
        $commands = new RegisterMetadataWarmCommands(
            cacheDir  : $this->cacheDir,
            configHash: 'test-hash',
        );

        $result = ($commands()['metadata:clear'])([]);

        self::assertStringContainsString('No metadata cache found', $result);
    }

    #[Test]
    public function warmAndClearMultipleClasses() : void
    {
        $commands = new RegisterMetadataWarmCommands(
            cacheDir  : $this->cacheDir,
            configHash: 'test-hash',
            classes   : [TestEntity::class, TestDto::class],
        );

        $warmResult = ($commands()['metadata:warm'])([]);
        self::assertStringContainsString('Compiled: 2', $warmResult);

        $clearResult = ($commands()['metadata:clear'])([]);
        self::assertStringContainsString('Removed 2', $clearResult);
    }

    protected function setUp() : void
    {
        $this->cacheDir = sys_get_temp_dir() . '/avax-metadata-test-' . uniqid();
    }

    protected function tearDown() : void
    {
        // Clean up test cache directory
        if (is_dir($this->cacheDir)) {
            $files = glob($this->cacheDir . '/**/*', GLOB_NOSORT);
            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            $dirs = glob($this->cacheDir . '/*', GLOB_ONLYDIR);
            if ($dirs !== false) {
                rsort($dirs);
                foreach ($dirs as $dir) {
                    rmdir($dir);
                }
            }
            if (is_dir($this->cacheDir)) {
                rmdir($this->cacheDir);
            }
        }
    }
}

// Test fixtures

#[Entity, Table(name: 'test_entities')]
final class TestEntity
{
    #[Id, Column(type: 'int')]
    public int $id;

    #[Column(type: 'string', nullable: false)]
    public string $name;
}

final class TestDto
{
    public string $value;
}
