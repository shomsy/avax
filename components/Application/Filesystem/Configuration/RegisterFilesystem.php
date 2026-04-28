<?php

declare(strict_types=1);

namespace Avax\Filesystem\Configuration;

use Avax\Container\Core\Capabilities\Binding\Binders\Binder;
use Avax\Container\Core\Capabilities\Binding\BindingRepository;
use Avax\Filesystem\Disks\Disk;
use Avax\Filesystem\Disks\ResolveDisk;
use Avax\Filesystem\Filesystem;
use Avax\Filesystem\FilesystemInterface;

final class RegisterFilesystem
{
    public function execute(BindingRepository $container) : void
    {
        $container->singleton(
            abstract: FilesystemConfig::class,
            concrete: static fn () => FilesystemConfig::defaults()
        );

        $container->singleton(
            abstract: ResolveDisk::class,
            concrete: static fn (Binder $binder) => new ResolveDisk(
                config: $binder->make(abstract: FilesystemConfig::class)
            )
        );

        $container->singleton(
            abstract: Disk::class,
            concrete: static fn (Binder $binder) => $binder->make(abstract: ResolveDisk::class)->execute()
        );

        $container->singleton(
            abstract: Filesystem::class,
            concrete: static fn (Binder $binder) => new Filesystem(
                disk: $binder->make(abstract: Disk::class)
            )
        );

        $container->singleton(
            abstract: FilesystemInterface::class,
            concrete: static fn (Binder $binder) => $binder->make(abstract: Filesystem::class)
        );
    }
}
