<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

enum SagaStartCondition: string
{
    case MANUAL   = 'manual';
    case EVENT    = 'event';
    case SCHEDULE = 'schedule';
    case COMMAND  = 'command';
}
