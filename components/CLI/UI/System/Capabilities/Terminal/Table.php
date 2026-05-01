<?php

declare(strict_types=1);

namespace Avax\Components\CLI\UI\System\Capabilities\Terminal;

final readonly class Table
{
    public function __construct(
        private array $headers,
        private array $rows,
    ) {}

    public function render(): void
    {
        $widths = $this->calculateWidths();
        $this->renderSeparator($widths);
        $this->renderRow($this->headers, $widths);
        $this->renderSeparator($widths);

        foreach ($this->rows as $row) {
            $this->renderRow($row, $widths);
        }

        $this->renderSeparator($widths);
    }

    private function calculateWidths(): array
    {
        $widths = array_map('strlen', $this->headers);
        foreach ($this->rows as $row) {
            foreach ($row as $i => $cell) {
                $widths[$i] = max($widths[$i], strlen((string) $cell));
            }
        }

        return $widths;
    }

    private function renderRow(array $row, array $widths): void
    {
        echo '|';
        foreach ($row as $i => $cell) {
            printf(' %s ', str_pad((string) $cell, $widths[$i]));
            echo '|';
        }

        echo "\n";
    }

    private function renderSeparator(array $widths): void
    {
        echo '+';
        foreach ($widths as $width) {
            echo str_repeat('-', $width + 2) . '+';
        }

        echo "\n";
    }
}
