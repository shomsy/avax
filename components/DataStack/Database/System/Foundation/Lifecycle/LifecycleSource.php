<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle;

/**
 * Source of a lifecycle registration.
 *
 * Tracks how a listener was declared: through the fluent DSL,
 * through compile-time attribute scanning, or through configuration.
 */
enum LifecycleSource: string
{
    case Dsl = 'dsl';
    case Attribute = 'attribute';
    case Configuration = 'configuration';
}
