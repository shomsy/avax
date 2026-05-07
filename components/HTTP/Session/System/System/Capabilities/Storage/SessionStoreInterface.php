<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\System\Capabilities\Storage;

interface SessionStoreInterface
{
    /**
     * @return array<string, mixed>
     */
    public function read(string $id) : array;

    /**
     * @param array<string, mixed> $data
     */
    public function write(string $id, array $data) : bool;

    public function destroy(string $id) : bool;
}
