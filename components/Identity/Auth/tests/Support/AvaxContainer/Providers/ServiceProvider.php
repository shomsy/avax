<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\Providers;

use Avax\Components\Application\Container\Core\Container;

abstract class ServiceProvider
{
    public Container $app;

    abstract public function register() : void;
}
