<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

use RuntimeException;

/**
 * SagaRecoveryFailure - reports failed saga recovery.
 */
final class SagaRecoveryFailure extends RuntimeException {}
