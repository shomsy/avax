<?php

/**
 * P1 Quick Fixes: Decouple "AvaX" from mostly-generic governance files.
 * Replaces "AvaX" with generic project/platform language.
 */

$files = [
    ".agents/how-to/architecture/how-to-engineering-laws.md",
    ".agents/how-to/architecture/how-to-architecture-decisions.md",
    ".agents/how-to/implementation/how-to-code-style.md",
    ".agents/how-to/implementation/how-to-clean-code.md",
    ".agents/how-to/verification/how-to-code-review.md",
    ".agents/how-to/verification/how-to-test-risk-based-behavioral-testing.md",
    ".agents/how-to/verification/how-to-data-systems.md",
    ".agents/how-to/documentation/how-to-write-self-explaining-architecture.md",
    ".agents/how-to/documentation/how-to-document.md",
];

$root = dirname(__DIR__, 2);

$replacements = [
    ["AvaX must", "The framework must"],
    ["AvaX MUST", "The framework MUST"],
    ["AvaX framework", "The framework"],
    ["AvaX component", "A component"],
    ["AvaX components", "Components"],
    ["AvaX project", "The project"],
    ["AvaX architecture", "The architecture"],
    ["AvaX governance", "The project's governance"],
    ["AvaX governance.", "The project's governance."],
    ["AvaX governance,", "The project's governance,"],
    ["AvaX-specific", "project-specific"],
    ["AvaX's own", "the framework's own"],
    ["AvaX-style", "enterprise-style"],
    ["AvaX-grade", "enterprise-grade"],
    ["for AvaX", "for the project"],
    ["in AvaX,", "in the project,"],
    ["in AvaX.", "in the project."],
    ["in AvaX", "in the project"],
    ["of AvaX", "of the project"],
    ["the AvaX", "the"],
    ["across AvaX", "across the project"],
    ["into AvaX", "into the project"],
    ["within AvaX", "within the project"],
    ["as AvaX", "as the project"],
    ["using AvaX", "using the framework"],
    ["AvaX call sites", "Call sites"],
    ["AvaX Interpretation", "Interpretation"],
    ["In AvaX style", "In the project style"],
    ["In AvaX,", "In the project,"],
    ["In AvaX.", "In the project."],
    ["AvaX V5", "the project V5"],
    ["AvaX\\'s", "the project\\'s"],
    ["AvaX style,", "the project style,"],
];

$totalChanges = 0;
foreach ($files as $relative) {
    $filepath = $root . '/' . $relative;
    if (!file_exists($filepath)) {
        echo "SKIP (not found): $relative\n";
        continue;
    }
    $content = file_get_contents($filepath);
    $original = $content;
    $changes = 0;
    foreach ($replacements as [$from, $to]) {
        $count = 0;
        $content = str_replace($from, $to, $content, $count);
        $changes += $count;
    }
    if ($content !== $original) {
        file_put_contents($filepath, $content);
        $totalChanges += $changes;
        echo "UPDATED: $relative ($changes changes)\n";
    } else {
        echo "NO CHANGE: $relative\n";
    }
}

echo "\nTotal changes: $totalChanges\n";
echo "Done.\n";
