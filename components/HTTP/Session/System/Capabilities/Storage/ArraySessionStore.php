<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Storage;

/**
 * In-memory session store for testing purposes.
 */
final class ArraySessionStore implements SessionStoreInterface
{
    /** @var array<string, array<string, mixed>> */
    private array $sessions = [];

    /**
     * @return array<string, mixed>
     */
    public function read(string $id) : array
    {
        return $this->sessions[$id] ?? [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function write(string $id, array $data) : bool
    {
        $this->sessions[$id] = $data;

        return true;
    }

    public function destroy(string $id) : bool
    {
        unset($this->sessions[$id]);

        return true;
    }
}
