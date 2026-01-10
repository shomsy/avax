<?php

declare(strict_types=1);
namespace Avax\Container\Features\Operate\Config;

/**
 * Composite object combining configuration with infrastructure components.
 *
 * @see docs/Features/Operate/Config/ContainerWithInfrastructure.md#quick-summary
 */
final readonly class ContainerWithInfrastructure
{
    /**
     * @see docs/Features/Operate/Config/ContainerWithInfrastructure.md#method-__construct
     */
    public function __construct(
        public ContainerConfig $config,
        public mixed           $cacheManager,
        public mixed           $loggerFactory
    ) {}
}
