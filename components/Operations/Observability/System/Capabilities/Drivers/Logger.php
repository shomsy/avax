<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Drivers;

use Avax\Components\Operations\Observability\System\Capabilities\Logs\StructuredLogRecord;

final class Logger
{
    /**
     * @var list<StructuredLogRecord>
     */
    private array $records = [];

    /**
     * @var list<callable(StructuredLogRecord): void>
     */
    private array $handlers = [];

    public function handle(callable $handler) : self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    public function info(string $message, array $context = []) : StructuredLogRecord
    {
        return $this->log('info', $message, $context);
    }

    public function log(string $level, string $message, array $context = []) : StructuredLogRecord
    {
        $record          = new StructuredLogRecord($level, $message, $context);
        $this->records[] = $record;

        foreach ($this->handlers as $handler) {
            $handler($record);
        }

        return $record;
    }

    public function warning(string $message, array $context = []) : StructuredLogRecord
    {
        return $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []) : StructuredLogRecord
    {
        return $this->log('error', $message, $context);
    }

    public function debug(string $message, array $context = []) : StructuredLogRecord
    {
        return $this->log('debug', $message, $context);
    }

    /**
     * @return list<StructuredLogRecord>
     */
    public function records() : array
    {
        return $this->records;
    }

    public function clear() : void
    {
        $this->records = [];
    }
}
