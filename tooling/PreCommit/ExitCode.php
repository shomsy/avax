<?php

declare(strict_types=1);

namespace Avax\Tooling\PreCommit;

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
    public const int SUCCESS = 0;

    public const int FAILURE = 1;

    public const int INVALID_ARGUMENTS = 2;
}
