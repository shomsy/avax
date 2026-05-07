<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Telemetry;

final class TelemetryExporter
{
    /**
     * @var list<array<string, mixed>>
     */
    private array $exports = [];

    /**
     * @var (callable(array<string, mixed>): bool)|null
     */
    private mixed $exportFn = null;

    public function using(callable $exportFn) : self
    {
        $this->exportFn = $exportFn;

        return $this;
    }

    /**
     * @param list<array<string, mixed>> $items
     *
     * @return int
     */
    public function exportBatch(array $items) : int
    {
        $successCount = 0;

        foreach ($items as $item) {
            if ($this->export($item)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function export(array $data) : bool
    {
        $this->exports[] = $data;

        if ($this->exportFn !== null) {
            return (bool) ($this->exportFn)($data);
        }

        return true;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function exported() : array
    {
        return $this->exports;
    }
}
