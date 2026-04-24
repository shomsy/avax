<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Transactions;

final class TransactionProfiler
{
    /** @var list<array{name: string, started_at: float, finished_at: float|null, duration_ms: float|null}> */
    private array $transactions = [];

    public function start(string $name) : int
    {
        $this->transactions[] = [
            'name'        => $name,
            'started_at'  => microtime(as_float: true),
            'finished_at' => null,
            'duration_ms' => null,
        ];

        return array_key_last(array: $this->transactions);
    }

    public function stop(int $id) : void
    {
        if (! isset($this->transactions[$id])) {
            return;
        }

        $finishedAt = microtime(as_float: true);

        $this->transactions[$id]['finished_at'] = $finishedAt;
        $this->transactions[$id]['duration_ms'] = ($finishedAt - $this->transactions[$id]['started_at']) * 1000;
    }

    public function all() : array
    {
        return $this->transactions;
    }

    public function reset() : void
    {
        $this->transactions = [];
    }
}
