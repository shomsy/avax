<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition\Assembly;

use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompileContainer;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistry;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Invocation\FunctionCaller;
use Avax\Components\Application\Container\System\Capabilities\ResolutionPolicy;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\Runtime\DependencyPool;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ManageScopes;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ScopeStore;

/**
 * Built runtime collaborators for one container instance.
 */
final readonly class RuntimeAssembly
{
    public function __construct(public DependencyRegistry $registrations, public ScopeStore $scopeStore, public DependencyPool $servicePool, public ManageScopes $scopes, public FunctionCaller $caller, public ResolutionPolicy $policy, public CompileContainer $compiler, public ResolveDependency $resolver)
    {
    }
}
