<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\ResumeSaga;

use RuntimeException;

/**
 * SagaRecoveryFailure - reports failed saga recovery.
 */
final class SagaRecoveryFailure extends RuntimeException {}
