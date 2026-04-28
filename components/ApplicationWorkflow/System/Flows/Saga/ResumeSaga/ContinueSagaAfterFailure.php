<?php

declare(strict_types=1);

namespace Avax\Components\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

use RuntimeException;

/**
 * ContinueSagaAfterFailure - continues a recoverable saga after failure.
 */
final class ContinueSagaAfterFailure extends RuntimeException {}
