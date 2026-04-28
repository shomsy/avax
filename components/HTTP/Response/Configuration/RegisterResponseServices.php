<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Configuration;

use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistryInterface;
use Avax\Components\HTTP\Response\Capabilities\Streams\ResponseStreamFactory;
use Avax\Components\HTTP\Response\ResponseEmitter;
use Avax\Components\HTTP\Response\ResponseFactory;

final class RegisterResponseServices
{
    public function register(ServiceRegistryInterface $services) : void
    {
        $services->singleton(abstract: ResponseStreamFactory::class, concrete: ResponseStreamFactory::class);
        $services->singleton(abstract: ResponseFactory::class, concrete: ResponseFactory::class);
        $services->singleton(abstract: ResponseEmitter::class, concrete: ResponseEmitter::class);
    }
}
