<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Capabilities\Disks;

use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;
use DateTimeInterface;

interface Disk
{
    public function read(StoragePath $path) : string;

    public function write(StoragePath $path, string $content) : bool;

    public function delete(StoragePath $path) : bool;

    public function exists(StoragePath $path) : bool;

    public function copy(StoragePath $source, StoragePath $destination) : bool;

    public function move(StoragePath $source, StoragePath $destination) : bool;

    public function url(StoragePath $path) : string;

    public function temporaryUrl(StoragePath $path, DateTimeInterface $expires) : string;

    public function supportsTemporaryUrl() : bool;
}