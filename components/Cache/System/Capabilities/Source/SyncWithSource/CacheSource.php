<?php

declare(strict_types=1);

namespace Avax\Components\Cache\System\Capabilities\Source\SyncWithSource;

interface CacheSource
{
    public function load(CacheSourceKey $key) : mixed;

    public function write(CacheSourceKey $key, mixed $value) : void;

    public function delete(CacheSourceKey $key) : void;

    public function exists(CacheSourceKey $key) : bool;
}