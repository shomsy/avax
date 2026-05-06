#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$agentsDir = $root.'/.agents';

$requiredDocs = [
    'how-to-architecture.md',
    'how-to-design-components.md',
    'how-to-architecture-extension-with-ddd.md',
    'how-to-use-advanced-architecture-patterns.md',
    'how-to-clean-code.md',
    'how-to-coding-standards.md',
    'how-to-code-style.md',
    'how-to-unit-test.md',
    'how-to-system-security.md',
    'how-to-system-performance.md',
    'how-to-document.md',
    'how-to-production-readiness.md',
    'how-to-code-review.md',
];

$violations = [];

foreach ($requiredDocs as $doc) {
    $path = $agentsDir.'/how-to/'.$doc;
    if (! file_exists($path)) {
        $violations[] = "Missing: $path";
    }
}

$indexPath = $agentsDir.'/GOVERNANCE_INDEX.md';
$enforcementMapPath = $agentsDir.'/GOVERNANCE_ENFORCEMENT_MAP.md';

$optionalDocs = [
    $indexPath,
    $enforcementMapPath,
];

foreach ($optionalDocs as $doc) {
    if (! file_exists($doc)) {
        $violations[] = "Missing: $doc";
    }
}

if (empty($violations)) {
    echo "GREEN: Governance index is current.\n";
    exit(0);
}

echo "=== Governance Index Check ===\n\n";
echo "MISSING DOCUMENTS:\n";
foreach ($violations as $v) {
    echo "  - $v\n";
}
echo "\n";
echo "Run: php tooling/governance/check-governance-index-current.php\n";

exit(1);
