<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ContainerIntelligence;

use Avax\Components\Application\Container\System\ContainerInterface;

/**
 * Provides explainable intelligence about the DI container.
 */
final readonly class ContainerAnalyzer
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    /**
     * Explain why a service exists, how it's built, and whether it's worker-safe.
     */
    public function why(string $id) : ContainerDependencyExplanation
    {
        $whyData     = $this->container->why($id);
        $serviceDesc = $this->container->describeService($id);

        $dependencies = $whyData['dependencies'] ?? [];
        $scope        = $whyData['scope'] ?? 'singleton';
        $isShared     = $serviceDesc['shared'] ?? false;
        $isLazy       = $this->container->isLazy($id);
        $isDeferred   = $this->container->isDeferred($id);

        $workerSafe = $this->assessWorkerSafety($scope, $isShared, $dependencies);

        return new ContainerDependencyExplanation(
            serviceId   : $id,
            scope       : $scope,
            isShared    : $isShared,
            isLazy      : $isLazy,
            isDeferred  : $isDeferred,
            dependencies: $dependencies,
            workerSafe  : $workerSafe,
            explanation : $this->buildExplanation($id, $scope, $isShared, $workerSafe),
        );
    }

    private function assessWorkerSafety(string $scope, bool $isShared, array $dependencies) : bool
    {
        if ($scope === 'singleton' && $isShared) {
            return true;
        }

        if ($scope === 'request') {
            return true;
        }

        return ! $isShared;
    }

    private function buildExplanation(string $id, string $scope, bool $isShared, bool $workerSafe) : string
    {
        $parts = [];

        $parts[] = sprintf("Service: %s", $id);
        $parts[] = sprintf("  Scope: %s", $scope);
        $parts[] = sprintf("  Shared: %s", $isShared ? 'yes' : 'no');
        $parts[] = sprintf("  Worker-safe: %s", $workerSafe ? 'yes' : 'no');

        return implode("\n", $parts);
    }

    /**
     * Find all services that depend on the given service.
     *
     * @return list<string>
     */
    public function whoUses(string $id) : array
    {
        $data = $this->container->whoUses($id);

        return $data['dependents'] ?? [];
    }

    /**
     * Find what would break if this service was removed or changed.
     *
     * @return list<string>
     */
    public function whatBreaksIf(string $id) : array
    {
        $data = $this->container->whatBreaksIf($id);

        return $data['affected'] ?? [];
    }

    /**
     * @return list<string>
     */
    public function detectScopeViolations() : array
    {
        $violations = [];
        $scopeData  = $this->container->debugScope();

        foreach ($scopeData as $serviceId => $info) {
            $scope        = $info['scope'] ?? 'singleton';
            $dependencies = $info['dependencies'] ?? [];

            if ($scope === 'request') {
                foreach ($dependencies as $dep) {
                    $depScope = $this->getServiceScope($dep);
                    if ($depScope === 'singleton') {
                        $violations[] = new ScopeViolation(
                            service   : $serviceId,
                            dependency: $dep,
                            message   : sprintf(
                                            "Request-scoped '%s' depends on singleton '%s' — state may leak",
                                            $serviceId,
                                            $dep,
                                        ),
                        );
                    }
                }
            }
        }

        return $violations;
    }

    private function getServiceScope(string $id) : string
    {
        $desc = $this->container->describeService($id);

        return $desc['scope'] ?? 'singleton';
    }

    /**
     * Export the dependency graph.
     */
    public function exportGraph(string $format = 'text', string $rootId = '') : string
    {
        return $this->container->exportGraph(
            format: $format,
            kind  : 'dependency',
            id    : $rootId,
        );
    }
}
