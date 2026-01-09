<?php

declare(strict_types=1);
namespace Avax\Container\Features\Operate\Boot;

use Avax\Container\Features\Core\Contracts\ContainerInterface;

/**
 * Interface for container bootstrappers.
 *
 * @see docs_md/Features/Operate/Boot/ContainerBootstrapInterface.md#quick-summary
 */
interface ContainerBootstrapInterface
{
    /**
     * Bootstrap the container and return a configured instance.
     *
     * @see docs_md/Features/Operate/Boot/ContainerBootstrapInterface.md#method-bootstrap
     */
    public function bootstrap() : ContainerInterface;
}
