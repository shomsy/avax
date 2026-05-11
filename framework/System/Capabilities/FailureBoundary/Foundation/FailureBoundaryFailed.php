<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation;

use Throwable;

/**
 * FailureBoundaryFailed — Thrown when the failure boundary itself fails.
 */
final class FailureBoundaryFailed extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly Throwable $originalFailure,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
