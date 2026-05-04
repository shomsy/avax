<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ExternalState\System\Capabilities\ReadApiDescriptions;

use Avax\Framework\System\Capabilities\ExternalState\System\PublicSurface\State;

class Redis implements State
{
    public function __construct(string $url = '') {}

    public function get(string $key) : mixed { return null; }

    public function set(string $key, mixed $value, int $ttl = 0) : void {}

    public function delete(string $key) : void {}

    public function exists(string $key) : bool { return false; }

    public function increment(string $key, int $value = 1) : int { return 0; }

    public function expire(string $key, int $ttl) : void {}
}
