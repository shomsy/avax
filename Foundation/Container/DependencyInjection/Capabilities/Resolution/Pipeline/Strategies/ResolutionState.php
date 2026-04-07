<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Strategies;

/**
 * Resolution state machine stages.
 *
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
