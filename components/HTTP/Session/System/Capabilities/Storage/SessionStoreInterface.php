<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Storage;

interface SessionStoreInterface
{
    public function read(string $id) : array;

    public function write(string $id, array $data) : bool;

    public function destroy(string $id) : bool;
}
