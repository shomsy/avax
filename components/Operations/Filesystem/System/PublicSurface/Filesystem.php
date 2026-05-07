<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\PublicSurface;

use Avax\Components\Operations\Filesystem\System\Flows\CopyFile\CopyFile;
use Avax\Components\Operations\Filesystem\System\Flows\DeleteFile\DeleteFile;
use Avax\Components\Operations\Filesystem\System\Flows\ListDirectory\ListDirectory;
use Avax\Components\Operations\Filesystem\System\Flows\MoveFile\MoveFile;
use Avax\Components\Operations\Filesystem\System\Flows\ReadFile\ReadFile;
use Avax\Components\Operations\Filesystem\System\Flows\WriteFile\WriteFile;

final readonly class Filesystem
{
    public static function read(string $path) : string
    {
        return (new ReadFile())->read($path);
    }

    public static function write(string $path, string $content) : bool
    {
        return (new WriteFile())->write($path, $content);
    }

    public static function delete(string $path) : bool
    {
        return (new DeleteFile())->delete($path);
    }

    public static function copy(string $from, string $to) : bool
    {
        return (new CopyFile())->copy($from, $to);
    }

    public static function move(string $from, string $to) : bool
    {
        return (new MoveFile())->move($from, $to);
    }

    /**
     * @return list<string>
     */
    public static function list(string $path) : array
    {
        return (new ListDirectory())->list($path);
    }
}
