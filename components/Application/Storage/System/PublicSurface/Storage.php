<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\PublicSurface;

use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Capabilities\Disks\RegisteredDisks;
use Avax\Components\Application\Storage\System\Capabilities\Disks\ResolveDisk;
use Avax\Components\Application\Storage\System\Flows\CheckStoredObject\CheckStoredObject;
use Avax\Components\Application\Storage\System\Flows\CopyStoredObject\CopyStoredObject;
use Avax\Components\Application\Storage\System\Flows\DeleteStoredObject\DeleteStoredObject;
use Avax\Components\Application\Storage\System\Flows\GenerateStoredObjectUrl\GenerateStoredObjectUrl;
use Avax\Components\Application\Storage\System\Flows\GenerateTemporaryStoredObjectUrl\GenerateTemporaryStoredObjectUrl;
use Avax\Components\Application\Storage\System\Flows\MoveStoredObject\MoveStoredObject;
use Avax\Components\Application\Storage\System\Flows\ReadStoredObject\ReadStoredObject;
use Avax\Components\Application\Storage\System\Flows\WriteStoredObject\WriteStoredObject;
use Avax\Components\Application\Storage\System\Foundation\Failure\DiskNotFound;
use Avax\Components\Application\Storage\System\Foundation\Values\DiskName;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;
use DateTimeInterface;

final class Storage
{
    private static ?string          $defaultDisk = null;
    private static ?RegisteredDisks $registry    = null;

    public static function setDefaultDisk(string $name) : void
    {
        self::$defaultDisk = $name;
    }

    public static function registerDisk(string $name, Disk $disk) : void
    {
        $registry = self::getRegistry();
        $registry->register(new DiskName($name), $disk);
    }

    private static function getRegistry() : RegisteredDisks
    {
        if (self::$registry === null) {
            self::$registry = new RegisteredDisks();
        }

        return self::$registry;
    }

    public static function put(string $path, string $content) : bool
    {
        $disk   = self::defaultDisk();
        $writer = new WriteStoredObject($disk);

        return $writer->execute($path, $content);
    }

    public static function defaultDisk() : Disk
    {
        $name = self::$defaultDisk ?? 'local';

        return self::disk($name);
    }

    public static function disk(string $name) : Disk
    {
        $registry = self::getRegistry();

        $disk = $registry->get($name);

        if ($disk === null) {
            throw new DiskNotFound($name);
        }

        return $disk;
    }

    public static function get(string $path) : string
    {
        $disk   = self::defaultDisk();
        $reader = new ReadStoredObject($disk);

        return $reader->execute($path);
    }

    public static function exists(string $path) : bool
    {
        $disk    = self::defaultDisk();
        $checker = new CheckStoredObject($disk);

        return $checker->execute($path);
    }

    public static function delete(string $path) : bool
    {
        $disk    = self::defaultDisk();
        $deleter = new DeleteStoredObject($disk);

        return $deleter->execute($path);
    }

    public static function copy(string $source, string $destination) : bool
    {
        $disk   = self::defaultDisk();
        $copier = new CopyStoredObject($disk);

        return $copier->execute($source, $destination);
    }

    public static function move(string $source, string $destination) : bool
    {
        $disk  = self::defaultDisk();
        $mover = new MoveStoredObject($disk);

        return $mover->execute($source, $destination);
    }

    public static function url(string $path) : string
    {
        $disk      = self::defaultDisk();
        $generator = new GenerateStoredObjectUrl($disk);

        return $generator->execute($path);
    }

    public static function temporaryUrl(string $path, DateTimeInterface $expires) : string
    {
        $disk      = self::defaultDisk();
        $generator = new GenerateTemporaryStoredObjectUrl($disk);

        return $generator->execute($path, $expires);
    }
}