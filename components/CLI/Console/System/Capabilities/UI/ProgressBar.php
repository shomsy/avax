<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\UI;

/**
 * Progress bar component for CLI output.
 */
class ProgressBar
{
    private int $current = 0;
    private int $barWidth;

    public function __construct(
        private readonly int $total,
        int                  $barWidth = 50
    )
    {
        $this->barWidth = max(10, $barWidth);
    }

    /**
     * Advance the progress by a step.
     */
    public function advance(int $step = 1) : void
    {
        $this->current = min($this->current + $step, $this->total);
        $this->display();
    }

    /**
     * Display the current progress bar.
     */
    public function display() : void
    {
        $percent    = $this->total > 0 ? ($this->current / $this->total) : 0;
        $filledBars = (int) round($percent * $this->barWidth);
        $emptyBars  = $this->barWidth - $filledBars;

        $bar        = str_repeat('=', $filledBars) . str_repeat(' ', $emptyBars);
        $percentStr = sprintf('%3d%%', (int) ($percent * 100));

        echo "\r[{$bar}] {$percentStr} ({$this->current}/{$this->total})";

        if ($this->current >= $this->total) {
            echo PHP_EOL;
        }
    }

    /**
     * Set the current progress directly.
     */
    public function setProgress(int $current) : void
    {
        $this->current = min(max(0, $current), $this->total);
        $this->display();
    }

    /**
     * Finish the progress bar.
     */
    public function finish() : void
    {
        $this->current = $this->total;
        $this->display();
    }

    /**
     * Get current progress.
     */
    public function getCurrent() : int
    {
        return $this->current;
    }

    /**
     * Get total steps.
     */
    public function getTotal() : int
    {
        return $this->total;
    }
}
