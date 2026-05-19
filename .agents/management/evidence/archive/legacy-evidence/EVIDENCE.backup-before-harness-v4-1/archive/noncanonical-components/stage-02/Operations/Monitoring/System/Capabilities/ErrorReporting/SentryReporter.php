<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Monitoring\System\Capabilities\ErrorReporting;

use Throwable;
use function Sentry\captureException;

final readonly class SentryReporter
{
    public function capture(Throwable $throwable) : void
    {
        if (function_exists(function: 'Sentry\captureException')) {
            captureException($throwable);
        }
    }
}
