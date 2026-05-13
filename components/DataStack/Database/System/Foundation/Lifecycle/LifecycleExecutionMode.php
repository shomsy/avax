<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle;

/**
 * Execution mode for lifecycle listeners.
 *
 * Sync is the only active mode for V5.8.
 * Async/queued listeners are ROADMAP for V5.8+.
 */
enum LifecycleExecutionMode: string
{
    case Sync = 'sync';
}
