<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ContainerIntelligence;

/**
 * Explanation of a container service.
 */
final readonly class ContainerDependencyExplanation
{
    public function __construct(
        public string $serviceId,
        public string $scope,
        public bool   $isShared,
        public bool   $isLazy,
        public bool   $isDeferred,
        public array  $dependencies,
        public bool   $workerSafe,
        public string $explanation,
    ) {}
}
