<?php

declare(strict_types=1);

namespace Avax\Tests\Support\Tooling;

/**
 * Test helper that creates temporary git repositories with fixture files.
 *
 * Creates a real temp directory with git init, allows writing files,
 * staging, and committing. Cleans up on destruction or explicit call.
 */
final class CreatesTemporaryGitRepository
{
    private string $dir;
    private bool $cleaned = false;

    public function __construct(?string $prefix = null)
    {
        $base = sys_get_temp_dir() . '/avax-sdlc-test-' . ($prefix ?? '') . '-' . uniqid();
        mkdir($base, 0775, true);
        $this->dir = $base;
    }

    public function path(): string
    {
        return $this->dir;
    }

    public function initGit(): self
    {
        $this->exec('git init');
        $this->exec('git config user.email "test@avax.dev"');
        $this->exec('git config user.name "Test"');
        return $this;
    }

    public function writeFile(string $relative, string $content): self
    {
        $full = $this->dir . '/' . $relative;
        $dir = dirname($full);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($full, $content);
        return $this;
    }

    public function stageAll(): self
    {
        $this->exec('git add -A');
        return $this;
    }

    public function commit(string $message = 'test commit'): self
    {
        $this->exec('git commit -m ' . escapeshellarg($message) . ' --allow-empty');
        return $this;
    }

    public function modifyFile(string $relative, string $content): self
    {
        return $this->writeFile($relative, $content);
    }

    public function exec(string $command): string
    {
        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = proc_open($command, $descriptors, $pipes, $this->dir);
        if (! is_resource($process)) {
            return '';
        }
        $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        return trim($output);
    }

    public function cleanup(): void
    {
        if ($this->cleaned) {
            return;
        }
        $this->cleaned = true;
        if (is_dir($this->dir)) {
            $this->exec('rm -rf ' . escapeshellarg($this->dir));
        }
    }

    public function __destruct()
    {
        $this->cleanup();
    }
}
