<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\StartSaga;

use RuntimeException;

/**
 * SagaStartFailure - reports failed saga instance creation.
 */
final class SagaStartFailure extends RuntimeException {}
