<?php

declare(strict_types=1);

namespace Avax\Filesystem\Configuration;

use Avax\Container\Core\Capabilities\Binding\Binders\Binder;
use Avax\Container\Core\Capabilities\Binding\BindingRepository;
use Avax\Filesystem\Filesystem;
use Avax\Filesystem\Disks\Disk;
use Avax\Filesystem\Disks\Local\LocalDisk;

class RegisterFilesystem
{
    public function execute(BindingRepository $container) : void
    {
        $container->singleton(
            abstract: Disk::class,
            concrete: LocalDisk::class
        );

        $container->singleton(
            abstract: Filesystem::class,
            concrete: static fn (Binder $binder) => new Filesystem(
                disk: $binder->make(abstract: Disk::class)
            )
        );
    }
}