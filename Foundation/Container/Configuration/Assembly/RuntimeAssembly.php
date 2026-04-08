<?php

declare(strict_types=1);

namespace Avax\Container\Configuration\Assembly;

use Avax\Container\Compilation\CompileContainer;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistry;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolutionPolicy;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use Avax\Container\DependencyInjection\Injection\Invocation\FunctionCaller;
use Avax\Container\DependencyInjection\Scopes\ManageScopes;
use Avax\Container\DependencyInjection\Scopes\ScopeStore;
use Avax\Container\Runtime\ServicePool;

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
