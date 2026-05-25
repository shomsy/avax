<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Configuration\Builders;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Access\System\Configuration\Graphs\AccessGraph;

/**
 * RegisterAccessDependencies — registers all Access/Authorization DI services.
 *
 * Delegates to AccessGraph which assembles the complete Access capability tree:
 *   - AuthorizationEngine, PolicyEvaluator
 *   - All Require* boundaries (Authentication, Role, Permission, ResourceOwner,
 *     PhishingResistant, FreshMfa, AdminElevation)
 *   - RequireAccessPolicy orchestrator
 *   - Authorization capability
 *   - Access public surface
 */
final class RegisterAccessDependencies
{
    public static function register(ContainerInterface $container) : void
    {
        AccessGraph::register($container);
    }

    public static function boot(ContainerInterface $container) : void
    {
        AccessGraph::boot($container);
    }
}
