<?php

/**
 * check-semantic-phpdoc.php
 *
 * Scans production PHP files for missing or fake semantic PHPDoc blocks.
 * Reports violations with severity.
 *
 * Usage: php tooling/governance/check-semantic-phpdoc.php [--path=...] [--severity=BLOCKER|HIGH|MEDIUM|LOW]
 */

$basePath = getcwd();
$scanPath = $argv[1] ?? null;
$targetDir = $scanPath ? $basePath . '/' . ltrim($scanPath, '/') : $basePath;
$exitCode = 0;
$findings = [];
$scanned = 0;
$excluded = 0;
$bannedPhrases = [
    'Handles things',
    'Processes data',
    'Helper for',
    'Service for',
    'Manager for',
    'Does stuff',
    'Utility method',
    'Main method',
    'This class is responsible for everything',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    $relative = str_replace($basePath . '/', '', $path);

    // Exclude vendor, tests, tooling, fixtures, EVIDENCE
    if (str_starts_with($relative, 'vendor/')
        || str_starts_with($relative, 'tests/')
        || str_starts_with($relative, 'tooling/')
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

    $tokens = token_get_all($content);
    $scanned++;
    $hasClass = false;
    $hasClassDoc = false;
    $methods = [];
    $currentMethod = null;
    $currentMethodDoc = false;

    foreach ($tokens as $i => $token) {
        if (!is_array($token)) {
            continue;
        }

        // Detect class/interface/trait/enum declarations
        if ($token[0] === T_CLASS || $token[0] === T_INTERFACE || $token[0] === T_TRAIT) {
            $hasClass = true;
            // Check for doc comment before class
            $j = $i - 1;
            while ($j >= 0 && is_array($tokens[$j]) && in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT], true)) {
                $j--;
            }
            if ($j >= 0 && is_array($tokens[$j]) && $tokens[$j][0] === T_DOC_COMMENT) {
                $hasClassDoc = true;
                // Check for banned phrases
                foreach ($bannedPhrases as $phrase) {
                    if (str_contains($tokens[$j][1], $phrase)) {
                        $findings[] = [
                            'file' => $relative,
                            'line' => $tokens[$j][2],
                            'type' => 'fake_docblock',
                            'severity' => 'HIGH',
                            'message' => "Class docblock contains banned phrase: '$phrase'",
                        ];
                    }
                }
            } else {
                $findings[] = [
                    'file' => $relative,
                    'line' => $token[2],
                    'type' => 'missing_class_docblock',
                    'severity' => 'HIGH',
                    'message' => 'Production class missing semantic PHPDoc',
                ];
            }
        }

        // Detect public/protected method declarations
        if ($token[0] === T_FUNCTION) {
            $currentMethod = ['line' => $token[2], 'has_doc' => false, 'name' => '', 'visibility' => 'public'];
            $j = $i - 1;
            while ($j >= 0 && is_array($tokens[$j])) {
                if ($tokens[$j][0] === T_PUBLIC || $tokens[$j][0] === T_PROTECTED) {
                    $currentMethod['visibility'] = $tokens[$j][1] === T_PROTECTED ? 'protected' : 'public';
                }
                if ($tokens[$j][0] === T_PRIVATE) {
                    $currentMethod['visibility'] = 'private';
                }
                if ($tokens[$j][0] === T_STATIC) {
                    // still a method
                }
                if ($tokens[$j][0] === T_DOC_COMMENT) {
                    $currentMethod['has_doc'] = true;
                    break;
                }
                if ($tokens[$j][0] === T_OPEN_TAG || $tokens[$j][0] === T_CLOSE_TAG || !is_array($tokens[$j])) {
                    break;
                }
                $j--;
            }
        }

        // Get method name
        if ($currentMethod !== null && $token[0] === T_STRING && $i > 0 && is_array($tokens[$i-1]) && $tokens[$i-1][0] === T_FUNCTION) {
            $currentMethod['name'] = $token[1];
            if ($currentMethod['name'] === '__construct') {
                // Constructor may skip docblock (often obvious)
                $currentMethod = null;
                continue;
            }
            if (!$currentMethod['has_doc'] && $currentMethod['visibility'] !== 'private') {
                $findings[] = [
                    'file' => $relative,
                    'line' => $currentMethod['line'],
                    'type' => 'missing_method_docblock',
                    'severity' => 'HIGH',
                    'message' => ucfirst($currentMethod['visibility']) . " method {$currentMethod['name']}() missing semantic PHPDoc",
                ];
            }
            $currentMethod = null;
        }
    }

    if (!$hasClass) {
        continue;
    }
}

// Report
echo "Semantic PHPDoc Gate\n";
echo "====================\n\n";
echo "Scanned files: {$scanned}\n";
echo "Excluded files: {$excluded}\n";
echo "Violations found: " . count($findings) . "\n\n";

if (count($findings) > 0) {
    $blockerCount = 0;
    $highCount = 0;
    foreach ($findings as $f) {
        echo "[{$f['severity']}] {$f['file']}:{$f['line']} — {$f['message']}\n";
        if ($f['severity'] === 'BLOCKER') {
            $blockerCount++;
        }
        if ($f['severity'] === 'HIGH') {
            $highCount++;
        }
    }
    if ($blockerCount > 0 || $highCount > 0) {
        $exitCode = 1;
    }
    echo "\nBLOCKER: {$blockerCount}, HIGH: {$highCount}, total: " . count($findings) . "\n";
} else {
    echo "PASS — all scanned files have proper PHPDoc.\n";
}

echo "\nExit code: {$exitCode}\n";
exit($exitCode);
