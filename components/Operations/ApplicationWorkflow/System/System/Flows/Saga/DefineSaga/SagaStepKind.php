<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\DefineSaga;

enum SagaStepKind: string
{
    case ACTION       = 'action';
    case COMPENSATION = 'compensation';
    case APPROVAL     = 'approval';
    case NOTIFICATION = 'notification';
}
