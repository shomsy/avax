<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

/**
 * Resolution state machine stages.
 *
 * @see docs/Core/Kernel/ResolutionState.md#quick-summary
 */
enum ResolutionState : string
{
    case ContextualLookup = 'contextual';
    case DefinitionLookup = 'definition';
    case Autowire         = 'autowire';
    case Evaluate         = 'evaluate';
    case Instantiate      = 'instantiate';
    case Success          = 'success';
    case Failure          = 'failure';
    case NotFound         = 'not_found';
}
