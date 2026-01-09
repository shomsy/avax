<?php

declare(strict_types=1);
namespace Avax\Container\Features\Operate\Boot;

/**
 * Debug-specific bootstrapper that enables additional monitoring and logging.
 *
 * @see docs_md/Features/Operate/Boot/DebugContainerBootstrapper.md#quick-summary
 */
class DebugContainerBootstrapper extends ContainerBootstrapper
{
    /**
     * @see docs_md/Features/Operate/Boot/DebugContainerBootstrapper.md#method-__construct
     */
    public function __construct(
        string|null $cacheDir = null
    )
    {
        parent::__construct(policy: null, debug: true, cacheDir: $cacheDir);
    }
}
