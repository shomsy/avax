<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\UI;

/**
 * Powerful Console Table generator for CLI.
 * Restored from legacy Avax UI components.
 */
final class Table
{
    private array $headers = [];
    private array $rows    = [];
    private int   $padding = 2;

    public function setHeaders(array $headers) : self
    {
        $this->headers = $headers;

        return $this;
    }

    public function addRow(array $row) : self
    {
        $this->rows[] = $row;

        return $this;
    }

    public function render() : void
    {
        if (empty($this->headers) && empty($this->rows)) {
            return;
        }

        $columnWidths = $this->calculateColumnWidths();
        $this->renderSeparator($columnWidths);
        $this->renderRow($this->headers, $columnWidths);
        $this->renderSeparator($columnWidths);

        foreach ($this->rows as $row) {
            $this->renderRow($row, $columnWidths);
        }

        $this->renderSeparator($columnWidths);
    }

    private function calculateColumnWidths() : array
    {
        $widths = [];
        foreach ($this->headers as $index => $header) {
            $widths[$index] = strlen((string) $header);
        }

        foreach ($this->rows as $row) {
            foreach ($row as $index => $value) {
                $widths[$index] = max($widths[$index] ?? 0, strlen((string) $value));
            }
        }

        return array_map(fn ($w) => $w + $this->padding, $widths);
    }

    private function renderSeparator(array $widths) : void
    {
        $separator = '+';
        foreach ($widths as $width) {
            $separator .= str_repeat('-', $width + 1) . '+';
        }
        echo $separator . "\n";
    }

    private function renderRow(array $row, array $widths) : void
    {
        $line = '|';
        foreach ($widths as $index => $width) {
            $value = $row[$index] ?? '';
            $line  .= ' ' . str_pad((string) $value, $width) . '|';
        }
        echo $line . "\n";
    }
}
