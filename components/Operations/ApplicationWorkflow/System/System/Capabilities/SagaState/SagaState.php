<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\SagaState;

/**
 * Represents the possible states of a saga instance.
 */
enum SagaState: string
{
    case Running      = 'running';
    case Completed    = 'completed';
    case Failed       = 'failed';
    case Compensating = 'compensating';
    case Compensated  = 'compensated';
}
