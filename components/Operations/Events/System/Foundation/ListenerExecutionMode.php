<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Foundation;

/**
 * How a listener should be executed.
 *
 * V5.7: only Sync is active. Async and AfterCommit are ROADMAP.
 */
enum ListenerExecutionMode: string
{
    case Sync = 'sync';
}
