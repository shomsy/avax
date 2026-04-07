<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Resolution;

use ReflectionMethod;
use ReflectionProperty;

final readonly class ServiceBlueprint
{
    /**
     * @param list<ReflectionProperty> $injectableProperties
     * @param list<ReflectionMethod> $injectableMethods
     */
    public function __construct(
        public string $class,
        public ReflectionMethod|null $constructor = null,
        public array $injectableProperties = [],
        public array $injectableMethods = [],
        public bool $shared = false
    ) {}
}
