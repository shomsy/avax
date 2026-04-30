<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Configuration;

use Avax\Components\Application\Container\Core\Capabilities\Binding\Binders\Binder;
use Avax\Components\Application\Container\Core\Capabilities\Binding\BindingRepository;
use Avax\Components\Application\Filesystem\Disks\Disk;
use Avax\Components\Application\Filesystem\Disks\ResolveDisk;
use Avax\Components\Application\Filesystem\Filesystem;
use Avax\Components\Application\Filesystem\FilesystemInterface;

final class RegisterFilesystem
{
    public function execute(BindingRepository $bindingRepository) : void
    {
        $bindingRepository->singleton(
            abstract: FilesystemConfig::class,
            concrete: static fn () : FilesystemConfig => FilesystemConfig::defaults(),
        );

        $bindingRepository->singleton(
            abstract: ResolveDisk::class,
            concrete: static fn (Binder $binder) : ResolveDisk => new ResolveDisk(
                config: $binder->make(abstract: FilesystemConfig::class),
            ),
        );

        $bindingRepository->singleton(
            abstract: Disk::class,
            concrete: static fn (Binder $binder) => $binder->make(abstract: ResolveDisk::class)->execute(),
        );

        $bindingRepository->singleton(
            abstract: Filesystem::class,
            concrete: static fn (Binder $binder) : Filesystem => new Filesystem(
                disk: $binder->make(abstract: Disk::class),
            ),
        );

        $bindingRepository->singleton(
            abstract: FilesystemInterface::class,
            concrete: static fn (Binder $binder) => $binder->make(abstract: Filesystem::class),
        );
    }
}
