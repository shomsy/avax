<?php

declare(strict_types=1);

namespace Avax\Components\Secrets\System\Capabilities\Stores;

interface SecretStore
{
    public function get(string $key) : string;

    public function set(string $key, string $value) : void;

    public function has(string $key) : bool;

    public function forget(string $key) : void;
}
