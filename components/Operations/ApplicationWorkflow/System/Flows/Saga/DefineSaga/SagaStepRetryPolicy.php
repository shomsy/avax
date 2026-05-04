<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

enum SagaStepRetryPolicy: string
{
    case NONE = 'none';
    case IMMEDIATE = 'immediate';
    case EXPONENTIAL = 'exponential';
    case LINEAR = 'linear';
}
