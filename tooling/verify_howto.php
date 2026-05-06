<?php

declare(strict_types=1);

$mappings = [
    '.agents/how-to/how-to-architecture.md' => '.agents/.rules/governance/architecture/how-to-architecture.md',
    '.agents/how-to/how-to-architecture-extension.md' => '.agents/.rules/governance/architecture/how-to-architecture-extension.md',
    '.agents/how-to/how-to-coding-standards.md' => '.agents/.rules/governance/standards/coding/how-to-coding-standards.md',
    '.agents/how-to/how-to-clean-code.md' => '.agents/.rules/governance/standards/coding/how-to-clean-code.md',
    '.agents/how-to/how-to-code-style.md' => '.agents/.rules/governance/standards/coding/how-to-code-style.md',
    '.agents/how-to/how-to-code-review.md' => '.agents/.rules/governance/standards/review/how-to-code-review.md',
    '.agents/how-to/how-to-document.md' => '.agents/.rules/governance/standards/documentation/how-to-document.md',
    '.agents/how-to/how-to-unit-test.md' => '.agents/.rules/governance/standards/testing/how-to-unit-test.md',
];

echo "🔍 Verifying file integrity...\n\n";

$allMatch = true;

foreach ($mappings as $source => $target) {
    if (! file_exists($source)) {
        echo "❌ Source missing: $source\n";
        $allMatch = false;

        continue;
    }

    if (! file_exists($target)) {
        echo "❌ Target missing: $target\n";
        $allMatch = false;

        continue;
    }

    $hashSource = md5_file($source);
    $hashTarget = md5_file($target);

    if ($hashSource === $hashTarget) {
        echo '✅ MATCH: '.basename($source)."\n";
    } else {
        echo '❌ MISMATCH: '.basename($source)." (Files differ!)\n";
        $allMatch = false;
    }
}

echo "\n";
if ($allMatch) {
    echo "✨ ALL FILES ARE IDENTICAL. The overwrite was 100% successful.\n";
} else {
    echo "⚠️ SOME FILES DO NOT MATCH. Please check permissions.\n";
}
