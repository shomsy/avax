<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\StateReset;

use Avax\Components\Application\Container\System\PublicSurface\Container;
use Avax\Components\Application\Facade\System\Foundation\Facade;
use Avax\Framework\System\Capabilities\ExternalState\System\PublicSurface\ExternalState;
use Avax\Framework\System\Capabilities\ResourceGovernance\System\PublicSurface\ResourceGovernor;
use Avax\Framework\System\Capabilities\Runtime\GracefulShutdown\System\Capabilities\ShutdownSequence;

/**
 * StaticStateReset — Resets global static state in Container and Facades.
 *
 * This class acts as a bridge between the StateResetRegistry (object-based)
 * and the static-only reset methods in core components.
 */
final class StaticStateReset implements ResettableState
{
    public function resetState() : void
    {
        // 1. Reset the global container instance in shortcuts and Container facade
        if (function_exists('resetAppInstance')) {
            resetAppInstance();
        } else {
            Container::reset();
        }

        // 2. Clear all resolved instances in all Facades
        Facade::reset();

        // 3. Reset ResourceGovernor (memory snapshots, request counts)
        ResourceGovernor::reset();

        // 4. Reset ExternalState adapters
        ExternalState::reset();

        // 5. Reset ShutdownSequence (draining flags, callbacks)
        ShutdownSequence::reset();
    }
}
