<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\Filesystem\System\Flows\CopyFile\CopyFile;
use Avax\Components\Operations\Filesystem\System\Flows\DeleteFile\DeleteFile;
use Avax\Components\Operations\Filesystem\System\Flows\ListDirectory\ListDirectory;
use Avax\Components\Operations\Filesystem\System\Flows\MoveFile\MoveFile;
use Avax\Components\Operations\Filesystem\System\Flows\ReadFile\ReadFile;
use Avax\Components\Operations\Filesystem\System\Flows\WriteFile\WriteFile;

/**
 * FilesystemServiceProvider — registers Operations/Filesystem component dependencies.
 *
 * Registers all filesystem Flow operations as singletons. Each Flow has no
 * external dependencies and operates directly on the filesystem.
 */
final class FilesystemServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // ReadFile — Flow to read file contents with no external dependencies
        $container->singleton(ReadFile::class, static fn () : ReadFile => new ReadFile());

        // WriteFile — Flow to write file contents with no external dependencies
        $container->singleton(WriteFile::class, static fn () : WriteFile => new WriteFile());

        // DeleteFile — Flow to delete files with no external dependencies
        $container->singleton(DeleteFile::class, static fn () : DeleteFile => new DeleteFile());

        // CopyFile — Flow to copy files with no external dependencies
        $container->singleton(CopyFile::class, static fn () : CopyFile => new CopyFile());

        // MoveFile — Flow to move files with no external dependencies
        $container->singleton(MoveFile::class, static fn () : MoveFile => new MoveFile());

        // ListDirectory — Flow to list directory contents with no external dependencies
        $container->singleton(ListDirectory::class, static fn () : ListDirectory => new ListDirectory());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
