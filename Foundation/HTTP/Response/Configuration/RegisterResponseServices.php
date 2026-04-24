<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Configuration;

use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistryInterface;
use Avax\HTTP\Response\Capabilities\Streams\ResponseStreamFactory;
use Avax\HTTP\Response\ResponseEmitter;
use Avax\HTTP\Response\ResponseFactory;

final class RegisterResponseServices
{
    public function register(ServiceRegistryInterface $services) : void
    {
        $services->singleton(abstract: ResponseStreamFactory::class, concrete: ResponseStreamFactory::class);
        $services->singleton(abstract: ResponseFactory::class, concrete: ResponseFactory::class);
        $services->singleton(abstract: ResponseEmitter::class, concrete: ResponseEmitter::class);
    }
}
