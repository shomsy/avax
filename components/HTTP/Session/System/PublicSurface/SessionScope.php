<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\PublicSurface;

use Avax\Components\HTTP\Session\System\Capabilities\Storage\SessionStoreInterface;

/**
 * Session scope – the internal engine behind the Session PublicSurface.
 * Manages the in-memory session state and delegates persistence to SessionStoreInterface.
 */
final class SessionScope
{
    private bool $started = false;

    private string $id = '';

    private array $data = [];

    public function __construct(
        private readonly SessionStoreInterface $store,
    ) {}

    public function start() : bool
    {
        if ($this->started) {
            return true;
        }

        if (session_status() === PHP_SESSION_NONE) {
            if (! session_start()) {
                return false;
            }
        }

        $this->id = session_id() ?: '';
        $this->data = $this->store->read($this->id);
        $this->started = true;

        return true;
    }

    public function isStarted() : bool
    {
        return $this->started;
    }

    public function id() : string
    {
        return $this->id;
    }

    public function has(string $key) : bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function all() : array
    {
        return $this->data;
    }

    public function set(string $key, mixed $value) : void
    {
        $this->data[$key] = $value;
        $this->sync();
    }

    public function forget(string $key) : void
    {
        unset($this->data[$key]);
        $this->sync();
    }

    public function clear() : void
    {
        $this->data = [];
        $this->sync();
    }

    public function destroy() : void
    {
        $this->data = [];
        $this->store->destroy($this->id);
        $this->started = false;
        $this->id = '';
    }

    public function regenerate(bool $destroy = false) : bool
    {
        if (! $this->started) {
            return false;
        }

        $result = session_regenerate_id($destroy);

        if ($result) {
            $this->id = session_id() ?: '';
            $this->sync();
        }

        return $result;
    }

    public function save() : void
    {
        if ($this->started && $this->id !== '') {
            $this->store->write($this->id, $this->data);
        }
    }

    private function sync() : void
    {
        if ($this->started) {
            $_SESSION = $this->data;
        }
    }
}
