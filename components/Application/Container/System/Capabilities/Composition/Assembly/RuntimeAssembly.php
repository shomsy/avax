<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition\Assembly;

use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompileContainer;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\ServiceRegistry;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Invocation\FunctionCaller;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolutionPolicy;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ServiceResolver;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ManageScopes;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ScopeStore;
use Avax\Components\Application\Container\System\Capabilities\Runtime\ServicePool;

/**
 * Built runtime collaborators for one container instance.
 */
final readonly class RuntimeAssembly
{
    public ServiceResolver $resolver;
    public CompileContainer $compiler;
    public ResolutionPolicy $policy;
    public FunctionCaller  $caller;
    public ManageScopes    $scopes;
    public ServicePool     $servicePool;
    public ScopeStore      $scopeStore;
    public ServiceRegistry $registrations;

    public function __construct(
        ServiceRegistry $registrations,
        ScopeStore      $scopeStore,
        ServicePool     $servicePool,
        ManageScopes    $scopes,
        FunctionCaller  $caller,
        ResolutionPolicy $policy,
        CompileContainer $compiler,
        ServiceResolver $resolver,
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
