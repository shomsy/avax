<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Commands;

use Avax\Components\CLI\Console\System\PublicSurface\Command;
use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\PreCommit;
use Override;

/**
 * Command to run PreCommit discipline checks.
 */
class PreCommitCommand extends Command
{
    protected string $name = 'pre-commit';

    protected string $description = 'Run pre-commit discipline checks';

    protected string $signature = 'pre-commit [--fix] [--full] [--check=]';

    protected array $arguments = [];

    protected array $options = ['fix', 'full', 'check'];

    #[Override]
    protected function handle(): int
    {
        $dryRun = ! $this->hasOption('fix');
        $full = $this->hasOption('full');
        $check = $this->option('check');

        // Build config
        $config = new PreCommitConfig();
        $config->setDryRun($dryRun);

        if (is_string($check) && $check !== '') {
            // Disable all checks first
            foreach ($config->getEnabledChecks() as $checkName) {
                $config->disableCheck($checkName);
            }

            $config->enableCheck($check);
        }

        $this->info('Running Pre-Commit Discipline Checks...');
        $this->newLine();

        if ($dryRun) {
            $this->comment('Mode: Dry run (no auto-fix)');
        } else {
            $this->comment('Mode: Auto-fix enabled');
        }

        if ($full) {
            $this->comment('Scope: Full project');
        } else {
            $this->comment('Scope: Touched files only');
        }

        $this->newLine();

        // Run PreCommit
        $preCommit = new PreCommit($config, [], ! $full);
        $result = $preCommit->run();

        // Output summary
        $this->output->line($result->getSummaryText());

        return $result->isBlocked() ? self::FAILURE : self::SUCCESS;
    }
}
