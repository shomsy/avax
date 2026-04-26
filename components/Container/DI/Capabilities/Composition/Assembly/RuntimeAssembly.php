<?php

declare(strict_types=1);

namespace components\Container\DI\Capabilities\Composition\Assembly;

use components\Container\DI\Capabilities\Composition\Compilation\CompileContainer;
use components\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistry;
use components\Container\DI\Capabilities\Execution\Injection\Invocation\FunctionCaller;
use components\Container\DI\Capabilities\Resolution\ResolutionPolicy;
use components\Container\DI\Capabilities\Resolution\ServiceResolver;
use components\Container\DI\Capabilities\Runtime\Scopes\ManageScopes;
use components\Container\DI\Capabilities\Runtime\Scopes\ScopeStore;
use components\Container\DI\Capabilities\Runtime\ServicePool;

/**
 * Built runtime collaborators for one container instance.
 */
final readonly class RuntimeAssembly
{
    public ServiceResolver  $resolver;
    public CompileContainer $compiler;
    public ResolutionPolicy $policy;
    public FunctionCaller   $caller;
    public ManageScopes     $scopes;
    public ServicePool      $servicePool;
    public ScopeStore       $scopeStore;
    public ServiceRegistry  $registrations;

    public function __construct(
        ServiceRegistry  $registrations,
        ScopeStore       $scopeStore,
        ServicePool      $servicePool,
        ManageScopes     $scopes,
        FunctionCaller   $caller,
        ResolutionPolicy $policy,
        CompileContainer $compiler,
        ServiceResolver  $resolver
    )
    {
        $this->registrations = $registrations;
        $this->scopeStore    = $scopeStore;
        $this->servicePool   = $servicePool;
        $this->scopes        = $scopes;
        $this->caller        = $caller;
        $this->policy        = $policy;
        $this->compiler      = $compiler;
        $this->resolver      = $resolver;
    }
}
