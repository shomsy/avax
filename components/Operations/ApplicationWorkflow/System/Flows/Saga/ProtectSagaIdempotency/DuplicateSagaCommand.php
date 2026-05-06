<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ProtectSagaIdempotency;

use RuntimeException;

/**
 * DuplicateSagaCommand - reports a command-key collision that cannot safely replay a previous result.
 */
final class DuplicateSagaCommand extends RuntimeException
{
}
