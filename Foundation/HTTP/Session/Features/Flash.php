<?php
declare(strict_types=1);

namespace Avax\HTTP\Session\Features;

use Avax\HTTP\Session\Shared\Contracts\FeatureInterface;

/**
 * Minimal flash store for short-lived session messages.
 */
final class Flash implements FeatureInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function boot() : void {}

    public function terminate() : void
    {
        $this->flush();
    }

    public function flush() : void
    {
        $this->data = [];
    }

    public function getName() : string
    {
        return 'flash';
    }

    public function isEnabled() : bool
    {
        return true;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key) : bool
    {
        return array_key_exists($key, $this->data);
    }

    public function forget(string $key) : void
    {
        unset($this->data[$key]);
    }

    public function all() : array
    {
        return $this->data;
    }

    public function success(string $message) : void
    {
        $this->put(key: 'success', value: $message);
    }

    public function put(string $key, mixed $value) : void
    {
        $this->data[$key] = $value;
    }

    public function error(string $message) : void
    {
        $this->put(key: 'error', value: $message);
    }

    public function info(string $message) : void
    {
        $this->put(key: 'info', value: $message);
    }

    public function warning(string $message) : void
    {
        $this->put(key: 'warning', value: $message);
    }

    public function keep(string $key) : void {}

    public function reflash() : void {}
}
