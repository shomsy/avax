<?php

declare(strict_types=1);

namespace Avax\Components\ApplicationWorkflow\System\Flows\Saga\StartSaga;

use RuntimeException;

/**
 * SagaStartFailure - reports failed saga instance creation.
 */
final class SagaStartFailure extends RuntimeException {}
