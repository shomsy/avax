<?php

/**
 * check-large-unit-thresholds.php
 *
 * Scans production PHP files and reports classes/methods exceeding size thresholds.
 *
 * Usage: php tooling/governance/check-large-unit-thresholds.php
 */

$basePath = getcwd();
$exitCode = 0;
$findings = [];
$scanned = 0;
$excluded = 0;

$thresholds = [
    'class_lines' => 300,
    'method_lines' => 50,
    'constructor_deps' => 8,
    'public_surface_lines' => 150,
    'service_provider_lines' => 250,
    'builder_lines' => 300,
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    $relative = str_replace($basePath . '/', '', $path);

    // Exclude vendor, tests, local dot worktrees, and evidence archives.
    if (str_starts_with($relative, '.')
        || str_starts_with($relative, 'vendor/')
        || str_starts_with($relative, 'tests/')
        || str_starts_with($relative, 'EVIDENCE/')
        || str_starts_with($relative, 'examples/')
    ) {
        $excluded++;
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false || $content === '') {
        continue;
    }

    $scanned++;
    $lines = substr_count($content, "\n") + 1;

    $isPublicSurface = str_contains($relative, '/PublicSurface/');
    $isServiceProvider = str_contains($relative, 'ServiceProvider.php');
    $isBuilder = str_contains($relative, '/Builders/') || str_contains($relative, '/Configuration/Builders/');

    // Class lines threshold
    if ($lines > $thresholds['class_lines']) {
        $findings[] = [
            'file' => $relative,
            'lines' => $lines,
            'threshold' => $thresholds['class_lines'],
            'type' => 'class_too_large',
            'severity' => 'REVIEW',
            'message' => "Class is {$lines} lines (threshold: {$thresholds['class_lines']})",
        ];
    }

    // PublicSurface threshold
    if ($isPublicSurface && $lines > $thresholds['public_surface_lines']) {
        $findings[] = [
            'file' => $relative,
            'lines' => $lines,
            'threshold' => $thresholds['public_surface_lines'],
            'type' => 'public_surface_too_large',
            'severity' => 'REVIEW',
            'message' => "PublicSurface is {$lines} lines (threshold: {$thresholds['public_surface_lines']})",
        ];
    }

    // ServiceProvider threshold
    if ($isServiceProvider && $lines > $thresholds['service_provider_lines']) {
        $findings[] = [
            'file' => $relative,
            'lines' => $lines,
            'threshold' => $thresholds['service_provider_lines'],
            'type' => 'service_provider_too_large',
            'severity' => 'REVIEW',
            'message' => "ServiceProvider is {$lines} lines (threshold: {$thresholds['service_provider_lines']})",
        ];
    }

    // Builder threshold
    if ($isBuilder && $lines > $thresholds['builder_lines']) {
        $findings[] = [
            'file' => $relative,
            'lines' => $lines,
            'threshold' => $thresholds['builder_lines'],
            'type' => 'builder_too_large',
            'severity' => 'BLOCKER',
            'message' => "Builder is {$lines} lines (threshold: {$thresholds['builder_lines']} — BLOCKER until classified)",
        ];
    }

    // Constructor dependency count
    $tokens = token_get_all($content);
    $inFunction = false;
    $parenDepth = 0;
    $depCount = 0;
    foreach ($tokens as $i => $token) {
        if (!is_array($token)) {
            continue;
        }
        if ($token[0] === T_FUNCTION && isset($tokens[$i+2]) && is_array($tokens[$i+2]) && $tokens[$i+2][1] === '__construct') {
            $inFunction = true;
            $parenDepth = 0;
            $depCount = 0;
            continue;
        }
        if ($inFunction) {
            if ($token === '(') {
                $parenDepth++;
            } elseif ($token === ')') {
                $parenDepth--;
                if ($parenDepth <= 0) {
                    break;
                }
            } elseif ($token === ',') {
                $depCount++;
            }
        }
    }
    if ($depCount >= $thresholds['constructor_deps']) {
        $findings[] = [
            'file' => $relative,
            'deps' => $depCount + 1,
            'threshold' => $thresholds['constructor_deps'],
            'type' => 'constructor_bloat',
            'severity' => 'REVIEW',
            'message' => "Constructor has " . ($depCount + 1) . " dependencies (threshold: {$thresholds['constructor_deps']})",
        ];
    }
}

// Report
echo "Large Unit Threshold Gate\n";
echo "=========================\n\n";
echo "Scanned files: {$scanned}\n";
echo "Excluded files: {$excluded}\n";
echo "Findings: " . count($findings) . " (REVIEW = does not auto-fail, BLOCKER = must classify)\n\n";

$reviewCount = 0;
$blockerCount = 0;
foreach ($findings as $f) {
    echo "[{$f['severity']}] {$f['file']} — {$f['message']}\n";
    if ($f['severity'] === 'BLOCKER') {
        $blockerCount++;
    } else {
        $reviewCount++;
    }
}

echo "\nBLOCKER: {$blockerCount}, REVIEW: {$reviewCount}\n";
if ($blockerCount > 0) {
    echo "BLOCKER findings must be classified before GREEN.\n";
    $exitCode = 1;
}

echo "\nExit code: {$exitCode}\n";
exit($exitCode);
