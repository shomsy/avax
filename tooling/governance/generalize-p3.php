<?php

/**
 * P3 Heavy Refactor: Decouple "AvaX" from deeply mixed governance files.
 * These files have high AvaX coupling — apply safe pattern replacements.
 */

$root = dirname(__DIR__, 2);

$files = [
    ".agents/how-to/architecture/how-to-architecture.md",
    ".agents/how-to/components/how-to-design-components.md",
    ".agents/how-to/components/how-to-dogfooding.md",
    ".agents/how-to/modeling/how-to-model-flows.md",
    ".agents/how-to/architecture/how-to-architecture-extension-with-ddd.md",
    ".agents/how-to/architecture/how-to-events-listeners-event-sourcing-cqrs-realtime.md",
    ".agents/how-to/architecture/how-to-use-advanced-architecture-patterns.md",
    ".agents/how-to/architecture/how-to-use-ai-assisted-execution.md",
    ".agents/how-to/verification/how-to-system-security.md",
    ".agents/how-to/verification/how-to-system-performance.md",
];

$replacements = [
    // Document titles and headers
    ["# AvaX ", "# "],
    ["## AvaX ", "## "],
    ["### AvaX ", "### "],
    // Core binding rules
    ["AvaX must", "The framework must"],
    ["AvaX MUST", "The framework MUST"],
    ["AvaX should", "The framework should"],
    ["AvaX SHOULD", "The framework SHOULD"],
    ["AvaX requires", "The framework requires"],
    ["AvaX REQUIRES", "The framework REQUIRES"],
    // Identity statements
    ["AvaX framework", "The framework"],
    ["AvaX component", "A component"],
    ["AvaX components", "Components"],
    ["AvaX project", "The project"],
    ["AvaX architecture", "The architecture"],
    ["AvaX governance", "The project's governance"],
    // Adjectival usage
    ["AvaX-specific", "project-specific"],
    ["AvaX's own", "the framework's own"],
    ["AvaX-style", "enterprise-style"],
    ["AvaX-grade", "enterprise-grade"],
    // Prepositional phrases
    ["for AvaX", "for the project"],
    ["in AvaX,", "in the project,"],
    ["in AvaX.", "in the project."],
    [" in AvaX ", " in the project "],
    ["of AvaX", "of the project"],
    ["across AvaX", "across the project"],
    ["into AvaX", "into the project"],
    ["within AvaX", "within the project"],
    ["as AvaX", "as the project"],
    ["using AvaX", "using the framework"],
    ["the AvaX", "the"],
    // Possessive
    ["AvaX's", "the project's"],
    ["AvaX style,", "the project style,"],
    ["AvaX style.", "the project style."],
    ["In AvaX style", "In the project style"],
    ["In AvaX,", "In the project,"],
    ["In AvaX.", "In the project."],
    ["AvaX V5", "the project V5"],
    ["AvaX V4", "the project V4"],
    // Ending sentences
    ["AvaX.", "the project."],
    ["AvaX,", "the project,"],
    ["AvaX:", "the project:"],
    // At beginning of sentence (must keep caps pattern)
    ["AvaX is", "The framework is"],
    ["AvaX does", "The framework does"],
    ["AvaX has", "The framework has"],
    ["AvaX was", "The framework was"],
    ["AvaX can", "The framework can"],
    ["AvaX will", "The framework will"],
    ["AvaX should not", "The framework should not"],
    ["AvaX must not", "The framework must not"],
    ["AvaX uses", "The framework uses"],
    ["AvaX uses its", "The framework uses its"],
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
