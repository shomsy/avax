<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition\Assembly;

use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompileContainer;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistry;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Invocation\FunctionCaller;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolutionPolicy;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\Runtime\DependencyPool;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ManageScopes;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ScopeStore;

/**
 * Built runtime collaborators for one container instance.
 */
final readonly class RuntimeAssembly
{
    public ResolveDependency  $resolver;
    public CompileContainer $compiler;
    public ResolutionPolicy $policy;
    public FunctionCaller  $caller;
    public ManageScopes    $scopes;
    public DependencyPool     $servicePool;
    public ScopeStore      $scopeStore;
    public DependencyRegistry $registrations;

    public function __construct(
        DependencyRegistry $registrations,
        ScopeStore      $scopeStore,
        DependencyPool     $servicePool,
        ManageScopes    $scopes,
        FunctionCaller  $caller,
        ResolutionPolicy $policy,
        CompileContainer $compiler,
        ResolveDependency  $resolver,
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
