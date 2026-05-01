<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\PublicSurface;

use Avax\Components\Application\Pipeline\System\Capabilities\Hooks\PipelineHook;
use Closure;

final class Pipeline
{
    private static HookRegistry|null $hookRegistry = null;

    public static function beforeRoute(Closure $handler) : void
    {
        self::registry()->add('beforeRoute', $handler);
    }

    private static function registry() : HookRegistry
    {
        if (! self::$hookRegistry instanceof HookRegistry) {
            self::$hookRegistry = new HookRegistry();
        }

        return self::$hookRegistry;
    }

    public static function afterRoute(Closure $handler) : void
    {
        self::registry()->add('afterRoute', $handler);
    }

    public static function beforeController(Closure $handler) : void
    {
        self::registry()->add('beforeController', $handler);
    }

    public static function afterController(Closure $handler) : void
    {
        self::registry()->add('afterController', $handler);
    }

    public static function beforeResponse(Closure $handler) : void
    {
        self::registry()->add('beforeResponse', $handler);
    }

    public static function afterResponse(Closure $handler) : void
    {
        self::registry()->add('afterResponse', $handler);
    }

    public static function onException(Closure $handler) : void
    {
        self::registry()->add('onException', $handler);
    }

    public static function onTerminate(Closure $handler) : void
    {
        self::registry()->add('onTerminate', $handler);
    }

    public static function execute(string $hook, mixed $data = null) : mixed
    {
        return self::registry()->execute($hook, $data);
    }

    public static function hooks() : array
    {
        return self::registry()->all();
    }
}

final class HookRegistry
{
    /** @var array<string, list<Closure>> */
    private array $hooks = [];

    public function add(string $name, Closure $handler) : void
    {
        if (! isset($this->hooks[$name])) {
            $this->hooks[$name] = [];
        }

        $this->hooks[$name][] = $handler;
    }

    public function execute(string $hook, mixed $data = null) : mixed
    {
        $handlers = $this->hooks[$hook] ?? [];
        $result   = $data;

        foreach ($handlers as $handler) {
            $result = $handler($result);
        }

        return $result;
    }

    public function has(string $hook) : bool
    {
        return isset($this->hooks[$hook]) && count($this->hooks[$hook]) > 0;
    }

    public function count(string $hook) : int
    {
        return count($this->hooks[$hook] ?? []);
    }

    public function all() : array
    {
        return $this->hooks;
    }
}