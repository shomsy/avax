<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Composition\Assembly;

use Avax\Container\DI\Capabilities\Composition\Compilation\CompileContainer;
use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistry;
use Avax\Container\DI\Capabilities\Resolution\ResolutionPolicy;
use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;
use Avax\Container\DI\Capabilities\Execution\Injection\Invocation\FunctionCaller;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ManageScopes;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ScopeStore;
use Avax\Container\DI\Capabilities\Runtime\ServicePool;

/**
 * Built runtime collaborators for one container instance.
 */
final readonly class RuntimeAssembly
{
    public function __construct(
        public ServiceRegistry $registrations,
        public ScopeStore $scopeStore,
        public ServicePool $servicePool,
        public ManageScopes $scopes,
        public FunctionCaller $caller,
        public ResolutionPolicy $policy,
        public CompileContainer $compiler,
        public ServiceResolver $resolver
    ) {}
}
