<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Router\Routing;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use RuntimeException;

final class FakeContainer implements PsrContainerInterface
{
    public function get(string $id) : mixed
    {
        return new $id;
    }

    public function has(string $id) : bool
    {
        return class_exists($id);
    }

    public function make(string $abstract, array $parameters = []) : object
    {
        throw new RuntimeException('Not implemented.');
    }

    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        throw new RuntimeException('Not implemented.');
    }

    public function injectInto(object $target) : object
    {
        throw new RuntimeException('Not implemented.');
    }

    public function canInject(object $target) : bool
    {
        return false;
    }

    public function beginScope() : void {}

    public function endScope() : void {}

    public function instance(string $abstract, object $instance) : void
    {
        throw new RuntimeException('Not implemented.');
    }
}
