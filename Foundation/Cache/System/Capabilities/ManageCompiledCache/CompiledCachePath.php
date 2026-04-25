<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCompiledCache;

use InvalidArgumentException;

final class CompiledCachePath
{
    public function __construct(
        public readonly string $path
    )
    {
        $this->validate();
    }

    private function validate() : void
    {
        if ($this->path === '') {
            throw new InvalidArgumentException('Compiled cache path cannot be empty');
        }
    }

    public function toString() : string
    {
        return $this->path;
    }

    public function toTemporaryPath() : string
    {
        return $this->path . '.' . bin2hex(random_bytes(8)) . '.tmp';
    }

    public function directory() : string
    {
        return dirname($this->path);
    }

    public function filename() : string
    {
        return basename($this->path);
    }
}