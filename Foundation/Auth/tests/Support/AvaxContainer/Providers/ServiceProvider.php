<?php

declare(strict_types=1);

namespace Avax\Container\Providers;

use Avax\Container\Core\Container;

abstract class ServiceProvider
{
    protected Container $app;

    final public function setApp(Container $app) : void
    {
        $this->app = $app;
    }

    abstract public function register() : void;
}
