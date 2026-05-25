#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * check-intrusive-coupling.php — CLI wrapper for the intrusive coupling gate.
 *
 * Detects cross-component internal reach-through and dependency direction violations.
 *
 * Exit codes:
 *   0 — PASS (no violations)
 *   1 — FAIL (BLOCKER/HIGH/MEDIUM violations found)
 *
 * The class implementation lives in CheckIntrusiveCoupling.php.
 * This wrapper exists so the gate can be invoked consistently with other tooling scripts.
 */

require_once __DIR__.'/../../vendor/autoload.php';

use Avax\Tooling\Governance\CheckIntrusiveCoupling;

$checker = new CheckIntrusiveCoupling();
$result = $checker->check();

echo $result['status']."\n";

if (! empty($result['errors'])) {
    echo implode("\n", $result['errors'])."\n";
    exit(1);
}

exit(0);
