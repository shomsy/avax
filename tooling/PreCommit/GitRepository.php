<?php

declare(strict_types=1);

namespace Avax\Tooling\PreCommit;

final readonly class GitRepository
{
    public function __construct(
        private ProcessRunner $processRunner,
        private string $rootDirectory,
    ) {
    }

    public function assertInsideWorkTree(): void
    {
        $processResult = $this->processRunner->run(
            command: ['git', '-C', $this->rootDirectory, 'rev-parse', '--is-inside-work-tree'],
        );

        if (! $processResult->successful() || $processResult->stdout !== 'true') {
            throw new HookInstallerException('Not inside a Git work tree: '.$this->rootDirectory);
        }
    }

    public function hooksDirectory(): string
    {
        $processResult = $this->processRunner->run(
            command: ['git', '-C', $this->rootDirectory, 'rev-parse', '--git-path', 'hooks'],
        );

        if (! $processResult->successful() || $processResult->stdout === '') {
            $error = $processResult->stderr !== '' ? $processResult->stderr : 'Git did not return a hooks path.';

            throw new HookInstallerException($error);
        }

        $hooksDirectory = $processResult->stdout;

        if (! Path::isAbsolute($hooksDirectory)) {
            $hooksDirectory = Path::join($this->rootDirectory, $hooksDirectory);
        }

        return Path::normalize($hooksDirectory);
    }
}
