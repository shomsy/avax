<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

enum SagaRecoveryStrategy: string
{
    case RETRY      = 'retry';
    case COMPENSATE = 'compensate';
    case SKIP       = 'skip';
    case ABANDON    = 'abandon';
}
