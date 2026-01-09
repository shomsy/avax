<?php

declare(strict_types=1);
namespace Avax\Container\Features\Operate\Shutdown;

use Avax\Container\Features\Operate\Scope\ScopeManager;

/**
 * Terminate the container runtime by clearing scopes.
 *
 * This invokable action is typically executed at the end of an application lifecycle
 * (for example, after a request) to ensure scoped instances are released and don’t leak.
 *
 * @see docs_md/Features/Operate/Shutdown/TerminateContainer.md#quick-summary
 */
final readonly class TerminateContainer
{
    /**
     * Terminate the active scope system.
     *
     * @param ScopeManager $scope Scope facade used to terminate scoped instances.
     * @return void
     * @see docs_md/Features/Operate/Shutdown/TerminateContainer.md#method-__invoke
     */
    public function __invoke(ScopeManager $scope) : void
    {
        $scope->terminate();
    }
}
