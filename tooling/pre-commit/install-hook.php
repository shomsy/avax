#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Avax Pre-Commit Hook Installer
 *
 * Usage:
 *   php tooling/pre-commit/install-hook.php
 *   php tooling/pre-commit/install-hook.php --force
 *   php tooling/pre-commit/install-hook.php --dry-run
 *   php tooling/pre-commit/install-hook.php --uninstall
 *   php tooling/pre-commit/install-hook.php --uninstall --force
 *   php tooling/pre-commit/install-hook.php --php=/usr/bin/php8.3
 *   php tooling/pre-commit/install-hook.php --help
 */
final class ExitCode
{
    public const SUCCESS = 0;

    public const FAILURE = 1;

    public const INVALID_ARGUMENTS = 2;
}

final class HookInstallerException extends RuntimeException
{
}

final class Console
{
    public function info(string $message): void
    {
        $this->line("Info: {$message}");
    }

    public function line(string $message = ''): void
    {
        fwrite(STDOUT, $message.PHP_EOL);
    }

    public function success(string $message): void
    {
        $this->line("Success: {$message}");
    }

    public function warning(string $message): void
    {
        $this->line("Warning: {$message}");
    }

    public function error(string $message): void
    {
        fwrite(STDERR, "Error: {$message}".PHP_EOL);
    }
}

final class CommandOptions
{
    /**
     * @param  list<string>  $unknownOptions
     */
    public function __construct(
        public readonly bool $force,
        public readonly bool $uninstall,
        public readonly bool $dryRun,
        public readonly bool $help,
        public readonly string $phpBinary,
        public readonly array $unknownOptions,
    ) {
    }

    /**
     * @param  list<string>  $argv
     */
    public static function fromArgv(array $argv): self
    {
        $force = false;
        $uninstall = false;
        $dryRun = false;
        $help = false;
        $phpBinary = PHP_BINARY;
        $unknownOptions = [];

        foreach (array_slice($argv, 1) as $argument) {
            if ($argument === '--force') {
                $force = true;

                continue;
            }

            if ($argument === '--uninstall') {
                $uninstall = true;

                continue;
            }

            if ($argument === '--dry-run') {
                $dryRun = true;

                continue;
            }

            if ($argument === '--help' || $argument === '-h') {
                $help = true;

                continue;
            }

            if (str_starts_with($argument, '--php=')) {
                $phpBinary = trim(substr($argument, strlen('--php=')));

                continue;
            }

            $unknownOptions[] = $argument;
        }

        return new self(
            force         : $force,
            uninstall     : $uninstall,
            dryRun        : $dryRun,
            help          : $help,
            phpBinary     : $phpBinary,
            unknownOptions: $unknownOptions,
        );
    }
}

final class ProcessResult
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $stdout,
        public readonly string $stderr,
    ) {
    }

    public function successful(): bool
    {
        return $this->exitCode === 0;
    }
}

final class ProcessRunner
{
    /**
     * @param  list<string>  $command
     */
    public function run(array $command, ?string $workingDirectory = null): ProcessResult
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(
            command        : $command,
            descriptor_spec: $descriptorSpec,
            pipes          : $pipes,
            cwd            : $workingDirectory,
        );

        if (! is_resource($process)) {
            throw new HookInstallerException('Failed to start process: '.implode(' ', $command));
        }

        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        return new ProcessResult(
            exitCode: is_int($exitCode) ? $exitCode : ExitCode::FAILURE,
            stdout  : trim($stdout === false ? '' : $stdout),
            stderr  : trim($stderr === false ? '' : $stderr),
        );
    }
}

final class Filesystem
{
    public function ensureDirectoryExists(string $directory, int $permissions = 0775): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (! mkdir($directory, $permissions, true) && ! is_dir($directory)) {
            throw new HookInstallerException("Failed to create directory: {$directory}");
        }
    }

    public function assertReadableFile(string $file): void
    {
        if (! is_file($file)) {
            throw new HookInstallerException("File does not exist: {$file}");
        }

        if (! is_readable($file)) {
            throw new HookInstallerException("File is not readable: {$file}");
        }
    }

    public function assertExecutable(string $file): void
    {
        if (! is_file($file)) {
            throw new HookInstallerException("Executable does not exist: {$file}");
        }

        if (! is_executable($file)) {
            throw new HookInstallerException("File is not executable: {$file}");
        }
    }

    public function atomicWriteExecutable(string $targetFile, string $content): void
    {
        $directory = dirname($targetFile);
        $temporaryFile = tempnam($directory, basename($targetFile).'.tmp.');

        if ($temporaryFile === false) {
            throw new HookInstallerException("Failed to create temporary file in: {$directory}");
        }

        try {
            $bytesWritten = file_put_contents($temporaryFile, $content, LOCK_EX);

            if ($bytesWritten === false || $bytesWritten !== strlen($content)) {
                throw new HookInstallerException("Failed to write full hook content to: {$temporaryFile}");
            }

            if (! chmod($temporaryFile, 0755)) {
                throw new HookInstallerException("Failed to make hook executable: {$temporaryFile}");
            }

            if (! rename($temporaryFile, $targetFile)) {
                throw new HookInstallerException("Failed to move hook into place: {$targetFile}");
            }
        } finally {
            if (is_file($temporaryFile)) {
                @unlink($temporaryFile);
            }
        }
    }

    public function backupFile(string $file): string
    {
        $backupFile = sprintf(
            '%s.backup.%s',
            $file,
            date('Ymd-His')
        );

        if (! copy($file, $backupFile)) {
            throw new HookInstallerException("Failed to create backup: {$backupFile}");
        }

        return $backupFile;
    }

    public function readFile(string $file): string
    {
        $content = file_get_contents($file);

        if ($content === false) {
            throw new HookInstallerException("Failed to read file: {$file}");
        }

        return $content;
    }

    public function deleteFile(string $file): void
    {
        if (! is_file($file)) {
            return;
        }

        if (! unlink($file)) {
            throw new HookInstallerException("Failed to delete file: {$file}");
        }
    }
}

final class Path
{
    public static function normalize(string $path): string
    {
        $realPath = realpath($path);

        if ($realPath !== false) {
            return rtrim($realPath, DIRECTORY_SEPARATOR);
        }

        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    public static function join(string ...$parts): string
    {
        $cleanParts = [];

        foreach ($parts as $index => $part) {
            $part = $index === 0
                ? rtrim($part, DIRECTORY_SEPARATOR)
                : trim($part, DIRECTORY_SEPARATOR);

            if ($part !== '') {
                $cleanParts[] = $part;
            }
        }

        return implode(DIRECTORY_SEPARATOR, $cleanParts);
    }

    public static function isAbsolute(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
    }
}

final class Shell
{
    public static function quote(string $value): string
    {
        return "'".str_replace("'", "'\\''", $value)."'";
    }
}

final class GitRepository
{
    public function __construct(
        private readonly ProcessRunner $processRunner,
        private readonly string $rootDirectory,
    ) {
    }

    public function assertInsideWorkTree(): void
    {
        $result = $this->processRunner->run(
            command: ['git', '-C', $this->rootDirectory, 'rev-parse', '--is-inside-work-tree'],
        );

        if (! $result->successful() || $result->stdout !== 'true') {
            throw new HookInstallerException("Not inside a Git work tree: {$this->rootDirectory}");
        }
    }

    public function hooksDirectory(): string
    {
        $result = $this->processRunner->run(
            command: ['git', '-C', $this->rootDirectory, 'rev-parse', '--git-path', 'hooks'],
        );

        if (! $result->successful() || $result->stdout === '') {
            $error = $result->stderr !== '' ? $result->stderr : 'Git did not return a hooks path.';

            throw new HookInstallerException($error);
        }

        $hooksDirectory = $result->stdout;

        if (! Path::isAbsolute($hooksDirectory)) {
            $hooksDirectory = Path::join($this->rootDirectory, $hooksDirectory);
        }

        return Path::normalize($hooksDirectory);
    }
}

final class AvaxPreCommitHook
{
    private const MARKER = 'AVAX_PRE_COMMIT_HOOK=1';

    public static function marker(): string
    {
        return self::MARKER;
    }

    public static function isGeneratedByAvax(string $content): bool
    {
        return str_contains($content, self::MARKER);
    }

    public static function render(string $phpBinary): string
    {
        $template = <<<'HOOK'
#!/usr/bin/env sh
# Avax Pre-Commit Hook
# Generated by tooling/pre-commit/install-hook.php
# Do not edit this file manually. Re-run the installer instead.
# {{AVAX_MARKER}}

set -eu

HOOK_DIR = "$(CDPATH= cd -- "$(dirname-- "$0")" && pwd)"
PROJECT_ROOT = "$(git -C "$HOOK_DIR /../.." rev-parse --show-toplevel 2>/dev/null || true)"

if [-z "$PROJECT_ROOT" ] then
    PROJECT_ROOT = "$(cd "$HOOK_DIR /../.." && pwd)"
fi

PRE_COMMIT_SCRIPT = "$PROJECT_ROOT/tooling/pre-commit/run-pre-commit.php"

if [! -f "$PRE_COMMIT_SCRIPT" ] then
    echo "Avax pre-commit error: script not found: $PRE_COMMIT_SCRIPT" >&2
    exit 1
fi

exec {
        {
            PHP_BINARY}
    } "$PRE_COMMIT_SCRIPT" "$@"
HOOK;

        return str_replace(
            search : ['{{AVAX_MARKER}}', '{{PHP_BINARY}}'],
            replace: [self::MARKER, Shell::quote($phpBinary)],
            subject: $template,
        );
    }
}

final class InstallHookCommand
{
    public function __construct(
        private readonly Console $console,
        private readonly Filesystem $filesystem,
        private readonly GitRepository $gitRepository,
        private readonly string $rootDirectory,
        private readonly string $sourceFile,
    ) {
    }

    public function run(CommandOptions $options): int
    {
        if ($options->help) {
            $this->printHelp();

            return ExitCode::SUCCESS;
        }

        if ($options->unknownOptions !== []) {
            $this->console->error('Unknown option(s): '.implode(', ', $options->unknownOptions));
            $this->printHelp();

            return ExitCode::INVALID_ARGUMENTS;
        }

        try {
            $this->gitRepository->assertInsideWorkTree();
            $this->filesystem->assertReadableFile($this->sourceFile);
            $this->filesystem->assertExecutable($options->phpBinary);

            $hooksDirectory = $this->gitRepository->hooksDirectory();
            $targetFile = Path::join($hooksDirectory, 'pre-commit');

            $this->filesystem->ensureDirectoryExists($hooksDirectory);

            if ($options->uninstall) {
                $this->uninstall(
                    targetFile: $targetFile,
                    force     : $options->force,
                    dryRun    : $options->dryRun,
                );

                return ExitCode::SUCCESS;
            }

            $this->install(
                targetFile: $targetFile,
                phpBinary : $options->phpBinary,
                force     : $options->force,
                dryRun    : $options->dryRun,
            );

            return ExitCode::SUCCESS;
        } catch (HookInstallerException $exception) {
            $this->console->error($exception->getMessage());

            return ExitCode::FAILURE;
        }
    }

    private function install(string $targetFile, string $phpBinary, bool $force, bool $dryRun): void
    {
        if (is_file($targetFile)) {
            $existingContent = $this->filesystem->readFile($targetFile);
            $generatedByAvax = AvaxPreCommitHook::isGeneratedByAvax($existingContent);

            if (! $force) {
                $owner = $generatedByAvax ? 'an existing Avax hook' : 'a non-Avax hook';

                throw new HookInstallerException(
                    "Pre-commit hook already exists and looks like {$owner}. Use --force to overwrite."
                );
            }

            if ($dryRun) {
                $this->console->warning("Would backup existing hook before overwrite: {$targetFile}");
            } else {
                $backupFile = $this->filesystem->backupFile($targetFile);
                $this->console->info("Backup created: {$backupFile}");
            }
        }

        $hookContent = AvaxPreCommitHook::render($phpBinary).PHP_EOL;

        if ($dryRun) {
            $this->console->info("Would install pre-commit hook: {$targetFile}");

            return;
        }

        $this->filesystem->atomicWriteExecutable($targetFile, $hookContent);

        $this->console->success('Pre-commit hook installed.');
        $this->console->line("Location: {$targetFile}");
        $this->console->line("Project: {$this->rootDirectory}");
    }

    private function uninstall(string $targetFile, bool $force, bool $dryRun): void
    {
        if (! is_file($targetFile)) {
            $this->console->info('No pre-commit hook found to remove.');

            return;
        }

        $existingContent = $this->filesystem->readFile($targetFile);
        $generatedByAvax = AvaxPreCommitHook::isGeneratedByAvax($existingContent);

        if (! $generatedByAvax && ! $force) {
            throw new HookInstallerException(
                'Refusing to remove a non-Avax pre-commit hook. Use --uninstall --force to remove it intentionally.'
            );
        }

        if ($dryRun) {
            $this->console->warning("Would remove hook: {$targetFile}");

            return;
        }

        if (! $generatedByAvax) {
            $backupFile = $this->filesystem->backupFile($targetFile);
            $this->console->info("Backup created before forced uninstall: {$backupFile}");
        }

        $this->filesystem->deleteFile($targetFile);
        $this->console->success('Pre-commit hook removed.');
    }

    private function printHelp(): void
    {
        $this->console->line(
            <<<'HELP'
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
}

function runInstaller(): int
{
    $rootDirectory = Path::normalize(dirname(__DIR__, 2));
    $sourceFile = Path::join($rootDirectory, 'tooling', 'pre-commit', 'run-pre-commit.php');

    $console = new Console();
    $filesystem = new Filesystem();
    $processRunner = new ProcessRunner();
    $gitRepository = new GitRepository(
        processRunner: $processRunner,
        rootDirectory: $rootDirectory,
    );

    $command = new InstallHookCommand(
        console      : $console,
        filesystem   : $filesystem,
        gitRepository: $gitRepository,
        rootDirectory: $rootDirectory,
        sourceFile   : $sourceFile,
    );

    /** @var list<string> $argv */
    $argv = $_SERVER['argv'] ?? [];

    return $command->run(CommandOptions::fromArgv($argv));
}

exit(runInstaller());
