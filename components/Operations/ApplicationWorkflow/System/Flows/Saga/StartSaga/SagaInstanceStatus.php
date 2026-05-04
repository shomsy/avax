<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga;

enum SagaInstanceStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case COMPENSATING = 'compensating';
    case COMPENSATED = 'compensated';
    case FAILED = 'failed';
    case TIMEOUT = 'timeout';
}
