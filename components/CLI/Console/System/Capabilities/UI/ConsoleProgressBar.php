<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\UI;

final class ConsoleProgressBar
{
    private int $current = 0;

    public function __construct(private readonly int $total, private readonly int $width = 50)
    {
    }

    public function advance(int $step = 1): void
    {
        $this->current += $step;
        $this->display();
    }

    public function display(): void
    {
        $percent = ($this->current / $this->total);
        $bar = (int)($percent * $this->width);

        $progress = str_repeat('=', $bar);
        $remaining = str_repeat(' ', $this->width - $bar);

        printf("\r[%s%s] %d%%", $progress, $remaining, (int)($percent * 100));

        if ($this->current >= $this->total) {
            echo "\n";
        }
    }
}
