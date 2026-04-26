<?php

declare(strict_types=1);

namespace components\ApplicationWorkflow\Saga\StartSaga;

use RuntimeException;

/**
 * SagaStartFailure - reports failed saga instance creation.
 */
final class SagaStartFailure extends RuntimeException {}
