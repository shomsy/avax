<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\Bindings\BindingRegistry;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Filesystem\System\Capabilities\Disks\ResolveDisk;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Application\Filesystem\System\PublicSurface\FilesystemInterface;

final class RegisterFilesystem
{
    public function execute(BindingRegistry $bindingRegistry): void
    {
        $bindingRegistry->singleton(
            abstract: FilesystemConfig::class,
            concrete: static fn (): FilesystemConfig => FilesystemConfig::defaults(),
        );

        $bindingRegistry->singleton(
            abstract: ResolveDisk::class,
            concrete: static fn (ResolveDependency $resolver): ResolveDisk => new ResolveDisk(
                filesystemConfig: $resolver->resolve(abstract: FilesystemConfig::class),
            ),
        );

        $bindingRegistry->singleton(
            abstract: Disk::class,
            concrete: static fn (ResolveDependency $resolver) => $resolver->resolve(abstract: ResolveDisk::class)->execute(),
        );

        $bindingRegistry->singleton(
            abstract: Filesystem::class,
            concrete: static fn (ResolveDependency $resolver): Filesystem => new Filesystem(
                disk: $resolver->resolve(abstract: Disk::class),
            ),
        );

        $bindingRegistry->singleton(
            abstract: FilesystemInterface::class,
            concrete: static fn (ResolveDependency $resolver) => $resolver->resolve(abstract: Filesystem::class),
        );
    }
}
