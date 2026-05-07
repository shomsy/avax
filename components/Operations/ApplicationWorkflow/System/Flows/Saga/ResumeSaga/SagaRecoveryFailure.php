<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

use RuntimeException;

class SagaRecoveryFailure extends RuntimeException
{
    public function __construct(string $message = 'Saga recovery failed.')
    {
        parent::__construct(message: $message);
    }
}
