<?php
declare(strict_types=1);

namespace Foundation\HTTP\Session\Recovery;

final class SessionRecoveryManager
{
    public function __construct(private RecoveryLog $log) {}

    public function backup(array $data) : void
    {
        $this->log->write('backup', $data);
    }

    public function restore() : ?array
    {
        return $this->log->read('backup');
    }
}
