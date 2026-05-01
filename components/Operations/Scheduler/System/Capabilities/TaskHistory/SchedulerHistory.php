<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Scheduler\System\Capabilities\TaskHistory;

final class SchedulerHistory
{
    private static array $history = [];

    public static function record(string $task, string $status, float $durationMs): void
    {
        self::$history[] = [
            'task' => $task,
            'status' => $status,
            'duration_ms' => $durationMs,
            'executed_at' => date('Y-m-d H:i:s'),
        ];

        if (count(self::$history) > 1000) {
            array_shift(self::$history);
        }
    }

    /**
     * @return list<array{task: string, status: string, duration_ms: float, executed_at: string}>
     */
    public static function last(int $limit = 100): array
    {
        return array_slice(self::$history, -$limit);
    }

    public static function clear(): void
    {
        self::$history = [];
    }
}
