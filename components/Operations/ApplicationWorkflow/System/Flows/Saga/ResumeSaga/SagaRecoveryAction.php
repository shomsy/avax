<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

enum SagaRecoveryAction: string
{
    case RETRY = 'retry';
    case COMPENSATE = 'compensate';
    case ABANDON = 'abandon';
    case MANUAL = 'manual';
}
