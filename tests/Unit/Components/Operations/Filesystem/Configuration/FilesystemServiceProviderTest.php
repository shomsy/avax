<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Filesystem\Configuration;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\Operations\Filesystem\System\Configuration\FilesystemServiceProvider;
use Avax\Components\Operations\Filesystem\System\Flows\CopyFile\CopyFile;
use Avax\Components\Operations\Filesystem\System\Flows\DeleteFile\DeleteFile;
use Avax\Components\Operations\Filesystem\System\Flows\ListDirectory\ListDirectory;
use Avax\Components\Operations\Filesystem\System\Flows\MoveFile\MoveFile;
use Avax\Components\Operations\Filesystem\System\Flows\ReadFile\ReadFile;
use Avax\Components\Operations\Filesystem\System\Flows\WriteFile\WriteFile;
use PHPUnit\Framework\TestCase;

final class FilesystemServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private FilesystemServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new FilesystemServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_read_file_resolves(): void
    {
        $flow = $this->container->get(ReadFile::class);

        $this->assertInstanceOf(ReadFile::class, $flow);
    }

    public function test_write_file_resolves(): void
    {
        $flow = $this->container->get(WriteFile::class);

        $this->assertInstanceOf(WriteFile::class, $flow);
    }

    public function test_delete_file_resolves(): void
    {
        $flow = $this->container->get(DeleteFile::class);

        $this->assertInstanceOf(DeleteFile::class, $flow);
    }

    public function test_copy_file_resolves(): void
    {
        $flow = $this->container->get(CopyFile::class);

        $this->assertInstanceOf(CopyFile::class, $flow);
    }

    public function test_move_file_resolves(): void
    {
        $flow = $this->container->get(MoveFile::class);

        $this->assertInstanceOf(MoveFile::class, $flow);
    }

    public function test_list_directory_resolves(): void
    {
        $flow = $this->container->get(ListDirectory::class);

        $this->assertInstanceOf(ListDirectory::class, $flow);
    }
}
