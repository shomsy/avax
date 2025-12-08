<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Locks;

class FileSessionLockManager implements SessionLockManagerInterface
{
    private string $lockDir;
    private array $locks = [];

    public function __construct(?string $lockDir = null)
    {
        $this->lockDir = $lockDir ?? sys_get_temp_dir() . '/session_locks';
        if (!is_dir($this->lockDir)) {
            mkdir($this->lockDir, 0755, true);
        }
    }

    public function acquire(string $sessionId, int $timeout = 30): bool
    {
        $lockFile = $this->getLockFilePath($sessionId);
        $startTime = time();
        
        while (true) {
            $file = @fopen($lockFile, 'x+');
            if ($file !== false) {
                fwrite($file, getmypid());
                fclose($file);
                $this->locks[$sessionId] = $lockFile;
                return true;
            }
            
            if ((time() - $startTime) > $timeout) {
                return false;
            }
            
            usleep(100000); // Sleep for 100ms before retrying
        }
    }

    public function release(string $sessionId): bool
    {
        if (!isset($this->locks[$sessionId])) {
            return false;
        }

        $lockFile = $this->locks[$sessionId];
        unset($this->locks[$sessionId]);
        
        return @unlink($lockFile);
    }

    public function isLocked(string $sessionId): bool
    {
        $lockFile = $this->getLockFilePath($sessionId);
        return file_exists($lockFile);
    }

    private function getLockFilePath(string $sessionId): string
    {
        return $this->lockDir . '/sess_' . md5($sessionId) . '.lock';
    }

    public function __destruct()
    {
        // Release all locks when the object is destroyed
        foreach (array_keys($this->locks) as $sessionId) {
            $this->release($sessionId);
        }
    }
}
