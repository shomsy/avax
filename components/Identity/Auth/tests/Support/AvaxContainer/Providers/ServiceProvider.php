<?php

declare(strict_types=1);

namespace Avax\Container\Providers;

use Avax\Container\Core\Container;

abstract class ServiceProvider
{
    public Container $app;

    abstract public function register() : void;
}
