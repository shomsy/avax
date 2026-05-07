<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Flows\RecordLog;

use Avax\Components\Operations\Observability\System\Capabilities\Logging\Logger;
use Avax\Components\Operations\Observability\System\Capabilities\Logs\StructuredLogRecord;

final readonly class RecordLog
{
    /**
     * @param array<string, mixed> $context
     */
    public function record(Logger $logger, string $level, string $message, array $context = []) : StructuredLogRecord
    {
        return $logger->log($level, $message, $context);
    }
}
