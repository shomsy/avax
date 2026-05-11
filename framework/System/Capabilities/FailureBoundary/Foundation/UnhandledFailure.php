<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation;

use Throwable;

/**
 * UnhandledFailure — Wraps an unhandled failure that was not swallowed.
 *
 * This exception ensures unhandled failures propagate rather than being silently ignored.
 */
final class UnhandledFailure extends \RuntimeException
{
    public function __construct(
        public readonly Throwable $originalFailure,
        public readonly FailureContext $context,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        if ($message === '') {
            $message = 'Unhandled failure in ' . $context->kind->value . ' boundary: ' . $originalFailure::class;
        }
        parent::__construct($message, $code, $previous);
    }
}
