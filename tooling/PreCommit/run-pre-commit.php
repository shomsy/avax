#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Pre-Commit Hook Runner
 *
 * This script is called by the git pre-commit hook.
 * It runs the PreCommit discipline system.
 *
 * Install hook:
 *   cp tooling/pre-commit/run-pre-commit.php .git/hooks/pre-commit
 *   chmod +x .git/hooks/pre-commit
 */
$rootDir = dirname(__DIR__, 2);

// Find vendor autoload
while (! file_exists($rootDir.'/vendor/autoload.php') && $rootDir !== dirname($rootDir)) {
    $rootDir = dirname($rootDir);
}

require_once $rootDir.'/vendor/autoload.php';

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\PreCommit;

// Parse arguments
$options = [
    'dry-run' => true,
    'auto-fix' => false,
    'full' => false,
    'help' => false,
];

foreach ($argv as $arg) {
    if ($arg === '--fix') {
        $options['auto-fix'] = true;
        $options['dry-run'] = false;
    } elseif ($arg === '--full') {
        $options['full'] = true;
    } elseif ($arg === '-h' || $arg === '--help') {
        $options['help'] = true;
    }
}

if ($options['help']) {
    echo "Pre-Commit Hook Runner\n";
    echo "====================\n\n";
    echo "Usage: {$argv[0]} [options]\n\n";
    echo "Options:\n";
    echo "  --fix    Apply auto-fixes (default: dry-run)\n";
    echo "  --full  Check entire project (default: touched files only)\n";
    echo "  -h      Show this help\n\n";
    exit(0);
}

// Configure
$config = new PreCommitConfig();
$config->setDryRun(! $options['auto-fix']);

// Run PreCommit
$preCommit = new PreCommit($config, [], ! $options['full']);
$result = $preCommit->run();

// Output summary
echo $result->getSummaryText();

// Exit with appropriate code
exit($result->isBlocked() ? 1 : 0);
