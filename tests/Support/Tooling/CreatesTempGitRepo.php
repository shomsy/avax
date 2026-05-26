<?php

declare(strict_types=1);

namespace Avax\Tests\Support\Tooling;

trait CreatesTempGitRepo
{
    private ?string $tempGitRepo = null;

    protected function createTempGitRepo(): string
    {
        $dir = sys_get_temp_dir() . '/avax_test_git_' . uniqid('', true);
        if (! mkdir($dir, 0777, true)) {
            throw new \RuntimeException("Failed to create temp directory: {$dir}");
        }

        // Initialize git repository
        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($dir) . " && " . escapeshellarg($git) . " init -b main 2>/dev/null");
        shell_exec("cd " . escapeshellarg($dir) . " && " . escapeshellarg($git) . " config user.name 'Test User' 2>/dev/null");
        shell_exec("cd " . escapeshellarg($dir) . " && " . escapeshellarg($git) . " config user.email 'test@example.com' 2>/dev/null");

        $this->tempGitRepo = $dir;

        return $dir;
    }

    protected function destroyTempGitRepo(): void
    {
        if ($this->tempGitRepo !== null && is_dir($this->tempGitRepo)) {
            $this->removeDirectory($this->tempGitRepo);
            $this->tempGitRepo = null;
        }
    }

    private function removeDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $path . '/' . $file;
            if (is_dir($filePath)) {
                $this->removeDirectory($filePath);
            } else {
                chmod($filePath, 0666);
                unlink($filePath);
            }
        }
        rmdir($path);
    }
}
