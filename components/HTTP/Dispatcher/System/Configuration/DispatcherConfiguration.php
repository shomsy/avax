<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Dispatcher\System\Configuration;

final readonly class DispatcherConfiguration
{
    public function __construct(
        public string $defaultNamespace = 'App\\Controllers',
        public string $defaultMethod = 'index',
        public bool   $autoResolveDependencies = true,
        public int    $maxResolutionDepth = 5,
    ) {}
}
