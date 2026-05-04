<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

enum SagaStatus: string
{
    case DRAFT = 'draft';
    case DEFINED = 'defined';
    case VALID = 'valid';
    case INVALID = 'invalid';
    case REGISTERED = 'registered';
}
