<?php

declare(strict_types=1);

namespace Avax\Tooling\PreCommit;
final readonly class InstallHookCommand
{
    public function __construct(
        private Console       $console,
        private Filesystem    $filesystem,
        private GitRepository $gitRepository,
        private string        $rootDirectory,
        private string        $sourceFile,
    )
    {
    }

    public function run(CommandOptions $commandOptions): int
    {
        if ($commandOptions->help) {
            $this->printHelp();

            return ExitCode::SUCCESS;
        }

        if ($commandOptions->unknownOptions !== []) {
            $this->console->error('Unknown option(s): ' . implode(', ', $commandOptions->unknownOptions));
            $this->printHelp();

            return ExitCode::INVALID_ARGUMENTS;
        }

        try {
            $this->gitRepository->assertInsideWorkTree();
            $this->filesystem->assertReadableFile($this->sourceFile);
            $this->filesystem->assertExecutable($commandOptions->phpBinary);

            $hooksDirectory = $this->gitRepository->hooksDirectory();
            $targetFile = Path::join($hooksDirectory, 'pre-commit');

            $this->filesystem->ensureDirectoryExists($hooksDirectory);

            if ($commandOptions->uninstall) {
                $this->uninstall(
                    targetFile: $targetFile,
                    force: $commandOptions->force,
                    dryRun: $commandOptions->dryRun,
                );

                return ExitCode::SUCCESS;
            }

            $this->install(
                targetFile: $targetFile,
                phpBinary: $commandOptions->phpBinary,
                force: $commandOptions->force,
                dryRun: $commandOptions->dryRun,
            );

            return ExitCode::SUCCESS;
        } catch (HookInstallerException $hookInstallerException) {
            $this->console->error($hookInstallerException->getMessage());

            return ExitCode::FAILURE;
        }
    }

    private function printHelp(): void
    {
        $this->console->line(<<<'HELP'
                                 Avax Pre-Commit Hook Installer
                                 
                                 Usage:
                                   php tooling/pre-commit/install-hook.php [options]
                                 
                                 Options:
                                   --force          Overwrite an existing pre-commit hook. Creates a backup first.
                                   --uninstall      Remove the installed Avax pre-commit hook.
                                   --dry-run        Show what would happen without changing files.
                                   --php=/path      PHP executable used by the Git hook. Defaults to current PHP binary.
                                   --help, -h       Show this help message.
                                 
                                 Safety:
                                   - Existing hooks are never overwritten unless --force is used.
                                   - Existing hooks are backed up before overwrite.
                                   - Uninstall removes only Avax-generated hooks unless --force is used.
                                   - Hook writes are atomic.
                                 HELP
        );
    }

    private function uninstall(string $targetFile, bool $force, bool $dryRun): void
    {
        if (!is_file($targetFile)) {
            $this->console->info('No pre-commit hook found to remove.');

            return;
        }

        $existingContent = $this->filesystem->readFile($targetFile);
        $generatedByAvax = AvaxPreCommitHook::isGeneratedByAvax($existingContent);

        if (!$generatedByAvax && !$force) {
            throw new HookInstallerException(
                'Refusing to remove a non-Avax pre-commit hook. Use --uninstall --force to remove it intentionally.'
            );
        }

        if ($dryRun) {
            $this->console->warning('Would remove hook: ' . $targetFile);

            return;
        }

        if (!$generatedByAvax) {
            $backupFile = $this->filesystem->backupFile($targetFile);
            $this->console->info('Backup created before forced uninstall: ' . $backupFile);
        }

        $this->filesystem->deleteFile($targetFile);
        $this->console->success('Pre-commit hook removed.');
    }

    private function install(string $targetFile, string $phpBinary, bool $force, bool $dryRun): void
    {
        if (is_file($targetFile)) {
            $existingContent = $this->filesystem->readFile($targetFile);
            $generatedByAvax = AvaxPreCommitHook::isGeneratedByAvax($existingContent);

            if (!$force) {
                $owner = $generatedByAvax ? 'an existing Avax hook' : 'a non-Avax hook';

                throw new HookInstallerException(
                    sprintf('Pre-commit hook already exists and looks like %s. Use --force to overwrite.', $owner)
                );
            }

            if ($dryRun) {
                $this->console->warning('Would backup existing hook before overwrite: ' . $targetFile);
            } else {
                $backupFile = $this->filesystem->backupFile($targetFile);
                $this->console->info('Backup created: ' . $backupFile);
            }
        }

        $hookContent = AvaxPreCommitHook::render($phpBinary) . PHP_EOL;

        if ($dryRun) {
            $this->console->info('Would install pre-commit hook: ' . $targetFile);

            return;
        }

        $this->filesystem->atomicWriteExecutable($targetFile, $hookContent);

        $this->console->success('Pre-commit hook installed.');
        $this->console->line('Location: ' . $targetFile);
        $this->console->line('Project: ' . $this->rootDirectory);
    }
}
