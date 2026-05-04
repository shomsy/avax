<?php

declare(strict_types=1);

namespace Avax\Tooling\PreCommit;
final readonly class CommandOptions
{
    /**
     * @param list<string> $unknownOptions
     */
    public function __construct(
        public bool   $force,
        public bool   $uninstall,
        public bool   $dryRun,
        public bool   $help,
        public string $phpBinary,
        public array  $unknownOptions,
    )
    {
    }

    /**
     * @param list<string> $argv
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
            force: $force,
            uninstall: $uninstall,
            dryRun: $dryRun,
            help: $help,
            phpBinary: $phpBinary,
            unknownOptions: $unknownOptions,
        );
    }
}
