<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\Capabilities\TaskRunner;

use Throwable;

final class TaskRunner
{
    /**
     * @var list<array{id: string, name: string, status: string, startedAt: ?int, completedAt: ?int, result: mixed,
     *      error: ?string}>
     */
    private array $history = [];

    /**
     * @param callable(): mixed $task
     *
     * @return array{id: string, status: string, result: mixed, error: ?string, duration: ?int}
     */
    public function run(callable $task, string $name = 'unnamed') : array
    {
        $id        = bin2hex(random_bytes(4));
        $startedAt = hrtime(true);

        try {
            $result   = $task();
            $duration = (hrtime(true) - $startedAt) / 1_000_000;

            $record = [
                'id'          => $id,
                'name'        => $name,
                'status'      => 'completed',
                'startedAt'   => time(),
                'completedAt' => time(),
                'result'      => $result,
                'error'       => null,
            ];

            $this->history[] = $record;

            return [
                'id'       => $id,
                'status'   => 'completed',
                'result'   => $result,
                'error'    => null,
                'duration' => (int) $duration,
            ];
        } catch (Throwable $e) {
            $duration = (hrtime(true) - $startedAt) / 1_000_000;

            $record = [
                'id'          => $id,
                'name'        => $name,
                'status'      => 'failed',
                'startedAt'   => time(),
                'completedAt' => time(),
                'result'      => null,
                'error'       => $e->getMessage(),
            ];

            $this->history[] = $record;

            return [
                'id'       => $id,
                'status'   => 'failed',
                'result'   => null,
                'error'    => $e->getMessage(),
                'duration' => (int) $duration,
            ];
        }
    }

    /**
     * @return list<array{id: string, name: string, status: string, startedAt: ?int, completedAt: ?int, result: mixed,
     *                        error: ?string}>
     */
    public function history() : array
    {
        return $this->history;
    }

    public function clearHistory() : void
    {
        $this->history = [];
    }
}
