<?php

declare(strict_types=1);

namespace Avax\Tooling\PreCommit;
final class Console
{
    public function info(string $message): void
    {
        $this->line('Info: ' . $message);
    }

    public function line(string $message = ''): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }

    public function success(string $message): void
    {
        $this->line('Success: ' . $message);
    }

    public function warning(string $message): void
    {
        $this->line('Warning: ' . $message);
    }

    public function error(string $message): void
    {
        fwrite(STDERR, 'Error: ' . $message . PHP_EOL);
    }
}
