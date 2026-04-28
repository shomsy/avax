<?php

declare(strict_types=1);

namespace Avax\Components\ApplicationWorkflow\System\Flows\Saga\CompleteSaga;

use RuntimeException;

/**
 * SagaCompletionFailure - reports invalid completion attempts.
 */
final class SagaCompletionFailure extends RuntimeException {}
