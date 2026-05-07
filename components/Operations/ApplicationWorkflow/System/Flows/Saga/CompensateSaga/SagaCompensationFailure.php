<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\CompensateSaga;

use RuntimeException;

/**
 * SagaCompensationFailure - reports failed compensation.
 */
final class SagaCompensationFailure extends RuntimeException {}
