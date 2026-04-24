<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\CompensateSaga;

use RuntimeException;

/**
 * SagaCompensationFailure - reports failed compensation.
 */
final class SagaCompensationFailure extends RuntimeException {}
