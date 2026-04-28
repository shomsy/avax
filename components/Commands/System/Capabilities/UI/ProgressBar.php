<?php

declare(strict_types=1);

namespace Avax\Components\Commands\System\Capabilities\UI;

/**
 * Interactive Console Progress Bar.
 * Restored from legacy Avax UI components.
 */
final class ProgressBar
{
    private int $total;
    private int $current = 0;
    private int $width   = 50;

    public function __construct(int $total)
    {
        $this->total = $total;
    }

    public function advance(int $step = 1) : void
    {
        $this->current += $step;
        $this->render();
    }

    public function render() : void
    {
        $percent = ($this->current / $this->total);
        $bar     = (int) ($percent * $this->width);

        $progress  = str_repeat('=', $bar);
        $remaining = str_repeat(' ', $this->width - $bar);

        printf("\r[%s%s] %d%% (%d/%d)", $progress, $remaining, (int) ($percent * 100), $this->current, $this->total);

        if ($this->current >= $this->total) {
            echo "\n";
        }
    }
}
