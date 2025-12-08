<?php
declare(strict_types=1);

namespace Foundation\HTTP\Session\Recovery;

use DateTimeImmutable;

final class RecoveryLog
{
    private string $path;

    public function __construct(string $path) { $this->path = $path; }

    public function write(string $type, array $data) : void
    {
        file_put_contents($this->path, json_encode(['type' => $type, 'time' => (new DateTimeImmutable())->format(DATE_ATOM), 'data' => $data]) . PHP_EOL, FILE_APPEND);
    }

    public function read(string $type) : ?array
    {
        $lines = file($this->path, FILE_IGNORE_NEW_LINES);
        foreach (array_reverse($lines) as $line) {
            $entry = json_decode($line, true);
            if ($entry['type'] === $type) return $entry['data'];
        }

        return null;
    }
}
