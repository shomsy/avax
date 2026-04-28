<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\Shared\Contracts;

use Avax\Components\HTTP\Session\SessionInterface as RootSessionInterface;

interface SessionInterface extends RootSessionInterface
{
    public function set(string $key, mixed $value, int|null $ttl = null) : void;

    public function remove(string $key) : void;

    public function clear() : void;

    public function destroy() : void;

    public function regenerateId(bool $deleteOldSession = true) : void;

    public function getId() : string;

    public function start() : bool;

    public function isStarted() : bool;
}
