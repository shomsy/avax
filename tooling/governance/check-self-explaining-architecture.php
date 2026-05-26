<?php

declare(strict_types=1);

/**
 * check-self-explaining-architecture.php
 *
 * Scans components/ for boundaries missing mandatory self-explaining documentation.
 * Reports findings with severity: BLOCKER / HIGH / MEDIUM / LOW / INFO.
 *
 * Exit codes: 0 = GREEN, 1 = findings exist
 */

$root = dirname(__DIR__, 2);
require_once $root . '/tooling/validation/governance-gate-baseline-lib.php';

$componentsDir = $root . '/components';
$docsDir = $root . '/docs';
$findings = [];
$scanned = 0;
$passed = 0;
$mode = avax_gate_mode($argv);
$baselinePath = $root . '/.agents/management/baselines/self-explaining-architecture-baseline.json';
$writeBaselinePath = avax_gate_arg_value($argv, '--write-baseline');

// Make $docsDir accessible in global scope for functions
$GLOBALS['docsDir'] = $docsDir;

// ---------------------------------------------------------------------------
// Collect all PHP class files for cross-reference checks
// ---------------------------------------------------------------------------
$allClassFiles = [];
$allClassNames = [];

function collectClassFiles(string $dir): void
{
    global $allClassFiles, $allClassNames;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $realPath = $file->getRealPath();
            $allClassFiles[] = $realPath;

            // Extract class name from file content (simple approach)
            $content = file_get_contents($realPath);
            if (preg_match('/(?:class|interface|trait|enum)\s+(\w+)/', $content, $matches)) {
                $allClassNames[] = $matches[1];
            }
        }
    }
}

// ---------------------------------------------------------------------------
// Helper: collect markdown files recursively
// ---------------------------------------------------------------------------
function collectMarkdownFiles(string $dir): array
{
    $files = [];
    if (!is_dir($dir)) {
        return $files;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() === 'md') {
            $files[] = $file->getRealPath();
        }
    }

    return $files;
}

// ---------------------------------------------------------------------------
// Helper: count words excluding headers, code fences, and front-matter
// ---------------------------------------------------------------------------
function countMeaningfulWords(string $content): int
{
    // Remove markdown headers
    $text = preg_replace('/^#{1,6}\s+.+$/m', '', $content);
    // Remove code blocks
    $text = preg_replace('/```[\s\S]*?```/m', '', $text);
    // Remove YAML front-matter
    $text = preg_replace('/^---\s*\n[\s\S]*?\n---\s*\n/m', '', $text);
    // Remove horizontal rules
    $text = preg_replace('/^---+$/m', '', $text);
    // Remove list markers
    $text = preg_replace('/^\s*[-*+]\s+/m', '', $text);
    // Remove blank lines
    $text = preg_replace('/^\s*$/m', '', $text);
    // Count words
    $words = str_word_count(strip_tags($text));
    return is_int($words) ? $words : 0;
}

// ---------------------------------------------------------------------------
// Helper: check if a boundary is "important" (requires documentation)
// ---------------------------------------------------------------------------
function isImportantBoundary(string $dir): bool
{
    $phpFiles = glob($dir . '/**/*.php');
    $fileCount = count($phpFiles ?: []);

    // Boundary with 10+ PHP files is important
    if ($fileCount >= 10) {
        return true;
    }

    // Boundary with System/ folder is important (canonical component shape)
    if (is_dir($dir . '/System')) {
        return true;
    }

    // Boundary with PublicSurface is important
    if (is_dir($dir . '/System/PublicSurface')) {
        return true;
    }

    // Boundary with Flows/ or Capabilities/ is important
    if (is_dir($dir . '/System/Flows') || is_dir($dir . '/System/Capabilities')) {
        return true;
    }

    return false;
}

// ---------------------------------------------------------------------------
// Helper: check if a boundary is exempt (too small to require docs)
// ---------------------------------------------------------------------------
function isExempt(string $dir): bool
{
    $phpFiles = glob($dir . '/*.php');
    $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
    $fileCount = count($phpFiles ?: []);

    // Single file or very small directory
    if ($fileCount <= 3 && count($subdirs ?: []) === 0) {
        return true;
    }

    // Foundation/ or InternalSystem/ exemption
    $basename = basename($dir);
    if (in_array($basename, ['Foundation', 'InternalSystem', 'ExportedCapabilities'], true)) {
        return true;
    }

    return false;
}

// ---------------------------------------------------------------------------
// Validate a README has required content
// ---------------------------------------------------------------------------
function validateReadme(string $path): array
{
    $content = file_get_contents($path);
    $issues = [];

    $lowerContent = strtolower($content);

    // Check for explicit ownership statement (not just the word "own")
    // Requires a clear responsibility pattern like "X is responsible for Y" or "X owns Y"
    $hasOwnershipStatement = preg_match(
        '/(?:is\s+responsible\s+for|owns?|own\s+responsibility|purpose|manages?|handles?|provides|coordinates)\s+\w+/i',
        $content
    );
    if (!$hasOwnershipStatement) {
        $issues[] = 'README lacks explicit ownership statement — needs a clear "This X owns/is responsible for Y" declaration';
    }

    // Check for negative space (what does NOT belong)
    // Require an explicit exclusion pattern
    $hasExclusionStatement = preg_match(
        '/(?:does\s+not\s+(?:own|handle|manage|provide|include|contain|expose|perform)|not\s+(?:responsible|owned|handled|managed)|non-?\s*goal|explicitly\s+(?:excluded|outside))\b/i',
        $content
    );
    if (!$hasExclusionStatement) {
        $issues[] = 'README does not explain what does NOT belong here (negative space required for AI)';
    }

    // Check for failure mode explanation
    $hasFailureMode = preg_match(
        '/(?:fail|failure|error|exception|recover|retry|fallback|must\s+fail|fail-closed|fail-open|throws|raises)\b/i',
        $content
    );
    if (!$hasFailureMode) {
        $issues[] = 'README does not explain failure behavior — how does this unit fail?';
    }

    // Check minimum content: at least 100 meaningful words (not just headers and code)
    $wordCount = countMeaningfulWords($content);
    if ($wordCount < 100) {
        $issues[] = "README has only ~$wordCount meaningful words — minimum 100 required for understanding";
    }

    // Check for dependency explanation if the unit has dependencies
    // Look for common dependency indicators
    $hasDependencyExplanation = preg_match(
        '/(?:depend|inject|require|uses?|consumes?|builds?\s+on|integrates?\s+with|relies?\s+on|service\s+provider|container|DI)\b/i',
        $content
    );

    // Check if the unit actually has dependencies (PHP files with constructor parameters)
    $dir = dirname($path);
    $phpFiles = glob($dir . '/**/*.php') ?: [];
    $hasConstructors = false;
    foreach ($phpFiles as $phpFile) {
        $phpContent = file_get_contents($phpFile);
        if (preg_match('/public\s+function\s+__construct\s*\(/', $phpContent)) {
            $hasConstructors = true;
            break;
        }
    }

    if ($hasConstructors && !$hasDependencyExplanation) {
        $issues[] = 'README does not explain dependencies — this unit has constructor dependencies that should be documented';
    }

    return $issues;
}

// ---------------------------------------------------------------------------
// Validate dictionary entries have required sections and content
// ---------------------------------------------------------------------------
function validateDictionaryEntry(string $path): array
{
    $content = file_get_contents($path);
    $issues = [];

    // Required sections must exist as actual headers (not just passing mentions)
    $requiredSections = [
        'What It Is' => 'Dictionary entry missing "What It Is" section',
        'What It Is NOT' => 'Dictionary entry missing "What It Is NOT" section (required for AI grounding)',
        'Common Confusion' => 'Dictionary entry missing "Common Confusion" section',
    ];

    foreach ($requiredSections as $section => $message) {
        // Check for section as a markdown header (## or ### or ####)
        $escaped = preg_quote($section, '/');
        if (!preg_match('/^#{2,6}\s+' . $escaped . '/m', $content)) {
            $issues[] = $message;
        }
    }

    // Check minimum content: at least 50 words of actual explanation per entry
    $wordCount = countMeaningfulWords($content);
    if ($wordCount < 50) {
        $issues[] = "Dictionary entry has only ~$wordCount meaningful words — minimum 50 required for grounding";
    }

    return $issues;
}

// ---------------------------------------------------------------------------
// Validate ADR has required sections
// ---------------------------------------------------------------------------
function validateAdr(string $path): array
{
    $content = file_get_contents($path);
    $issues = [];

    $requiredSections = [
        'Status' => 'ADR missing Status (proposed | accepted | deprecated | superseded)',
        'Context' => 'ADR missing Context (why this decision needed to be made)',
        'Decision' => 'ADR missing Decision (what was decided)',
        'Consequences' => 'ADR missing Consequences (what this means for the codebase)',
    ];

    foreach ($requiredSections as $section => $message) {
        if (!preg_match('/' . preg_quote($section, '/') . '/i', $content)) {
            $issues[] = $message;
        }
    }

    return $issues;
}

// ---------------------------------------------------------------------------
// Check for orphan doc references — docs that reference non-existent classes
// ---------------------------------------------------------------------------
function checkOrphanDocReferences(string $docPath, string $root): array
{
    $issues = [];
    $content = file_get_contents($docPath);

    // Skip this check for high-level docs that don't reference specific classes
    if (strpos($content, '`Avax\\') === false && strpos($content, 'class ') === false) {
        return $issues;
    }

    // Extract referenced class names from backtick-qualified names
    $referencedClasses = [];
    if (preg_match_all('/`Avax\\\\([^`<>\s:\'\"]+)`/', $content, $matches)) {
        foreach ($matches[1] as $match) {
            // Get the last segment (class name) from the namespace path
            $parts = explode('\\', $match);
            $className = end($parts);
            // Only consider PascalCase class-like names
            if (preg_match('/^[A-Z][a-zA-Z0-9]+$/', $className)) {
                $referencedClasses[] = $className;
            }
        }
    }

    // Also extract class/interface/trait/enum declarations from code blocks
    if (preg_match_all('/(?:class|interface|trait|enum)\s+([A-Z][a-zA-Z0-9]+)/', $content, $matches)) {
        foreach ($matches[1] as $match) {
            $referencedClasses[] = $match;
        }
    }

    $referencedClasses = array_unique($referencedClasses);

    // Skip common non-class terms and folder names
    $skipWords = [
        'The', 'This', 'That', 'These', 'Those', 'Each', 'All', 'Any', 'Some', 'No', 'Not', 'And', 'But', 'For',
        // AvaX folder names that are not classes
        'System', 'Flows', 'Capabilities', 'Foundation', 'Configuration', 'PublicSurface', 'Integrations',
        'Application', 'Framework', 'Components', 'Tests', 'Docs', 'Examples',
    ];

    foreach ($referencedClasses as $className) {
        if (in_array($className, $skipWords, true)) {
            continue;
        }

        // Check if any PHP file in the codebase defines this class
        $found = false;
        foreach ($GLOBALS['allClassNames'] as $existingClass) {
            if (strcasecmp($existingClass, $className) === 0) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $issues[] = "Documentation references class/interface '$className' which does not exist in the codebase";
        }
    }

    return $issues;
}

// ---------------------------------------------------------------------------
// Check for stale doc markers in production documentation
// ---------------------------------------------------------------------------
function checkStaleDocMarkers(string $docPath, string $root): array
{
    $issues = [];
    $content = file_get_contents($docPath);
    $relativePath = str_replace($root . '/', '', $docPath);

    // Skip files in obviously draft/WIP directories
    if (preg_match('#/(?:draft|wip|sandbox|experimental)/#i', $relativePath)) {
        return $issues;
    }

    $markers = [
        'TODO' => '/(?:^|\b)TODO\b/m',
        'DRAFT' => '/(?:^|\b)DRAFT\b/m',
        'WIP' => '/(?:^|\b)WIP\b/m',
        'PLACEHOLDER' => '/(?:^|\b)PLACEHOLDER\b/m',
        'COMING SOON' => '/(?:^|\b)COMING\s+SOON\b/m',
    ];

    foreach ($markers as $marker => $pattern) {
        if (preg_match($pattern, $content)) {
            $issues[] = "Production documentation contains '$marker' marker — documentation is incomplete";
        }
    }

    return $issues;
}

// ---------------------------------------------------------------------------
// Check if complex flows have corresponding Mermaid diagrams
// ---------------------------------------------------------------------------
function checkFlowDiagrams(string $flowDir, string $root): array
{
    $issues = [];
    $relativePath = str_replace($root . '/', '', $flowDir);
    $docsDir = $GLOBALS['docsDir'] ?? ($root . '/docs');

    // Collect PHP files using recursive iterator (glob ** doesn't work recursively in PHP)
    $phpFiles = [];
    $totalLines = 0;
    if (is_dir($flowDir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($flowDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $phpFiles[] = $file->getPathname();
                $totalLines += count(file($file->getPathname()));
            }
        }
    }
    $phpCount = count($phpFiles);

    // Only check flows that are complex enough to warrant diagrams
    if ($phpCount < 5 && $totalLines < 500) {
        return $issues;
    }

    // Determine the flow name and search for corresponding diagrams
    $flowName = basename($flowDir);
    // Also get the parent component name for more targeted matching
    $componentDir = dirname(dirname($flowDir));
    $componentName = basename($componentDir);

    // Search in docs/ for diagrams referencing this flow
    $docsFiles = collectMarkdownFiles($docsDir);
    $hasDiagram = false;

    foreach ($docsFiles as $docFile) {
        // Skip template/example docs that match everything
        if (strpos($docFile, '/new-component.md') !== false) {
            continue;
        }

        $docContent = file_get_contents($docFile);
        // Check if doc mentions this flow name AND has a mermaid diagram
        // Use more specific matching: the doc filename or path should reference the flow
        $docBasename = basename($docFile);
        $hasFlowReference = (
            stripos($docBasename, $flowName) !== false ||
            stripos($docContent, $flowName) !== false
        );
        if ($hasFlowReference && stripos($docContent, 'mermaid') !== false) {
            $hasDiagram = true;
            break;
        }
    }

    // Also check for local README or diagram files in the flow directory or parent docs/
    $localDocs = collectMarkdownFiles($flowDir);
    foreach ($localDocs as $localDoc) {
        $localContent = file_get_contents($localDoc);
        if (stripos($localContent, 'mermaid') !== false) {
            $hasDiagram = true;
            break;
        }
    }

    // Check parent component's docs/ subdirectories
    $componentDir = dirname(dirname($flowDir));
    $componentDocsFiles = collectMarkdownFiles($componentDir . '/docs');
    foreach ($componentDocsFiles as $compDoc) {
        $compContent = file_get_contents($compDoc);
        if (stripos($compContent, 'mermaid') !== false) {
            $hasDiagram = true;
            break;
        }
    }

    if (!$hasDiagram) {
        $issues[] = "Complex flow ($phpCount PHP files, ~$totalLines lines) lacks a corresponding Mermaid diagram in docs/ or local directory";
    }

    return $issues;
}

// ---------------------------------------------------------------------------
// Validate Mermaid blocks in markdown files
// ---------------------------------------------------------------------------
function validateMermaidBlocks(string $docPath, string $root): array
{
    $issues = [];
    $content = file_get_contents($docPath);
    $relativePath = str_replace($root . '/', '', $docPath);

    // Find all mermaid code blocks
    if (!preg_match_all('/```mermaid\s*\n([\s\S]*?)```/m', $content, $matches)) {
        // Check for unclosed mermaid blocks
        if (preg_match('/```mermaid\s*\n/m', $content) && !preg_match('/```mermaid\s*\n[\s\S]*```/m', $content)) {
            $issues[] = "Unclosed Mermaid code block detected";
        }
        return $issues;
    }

    $validDiagramTypes = [
        'graph',
        'sequenceDiagram',
        'stateDiagram',
        'classDiagram',
        'flowchart',
        'gantt',
        'pie',
        'erDiagram',
        'journey',
        'gitgraph',
        'mindmap',
        'timeline',
        'quadrantChart',
        'xychart',
    ];

    $blocks = $matches[1];
    foreach ($blocks as $index => $block) {
        $hasValidType = false;
        foreach ($validDiagramTypes as $type) {
            if (stripos($block, $type) !== false) {
                $hasValidType = true;
                break;
            }
        }

        if (!$hasValidType) {
            $issues[] = "Mermaid block #" . ($index + 1) . " does not contain a recognized diagram type keyword";
        }
    }

    return $issues;
}

// ---------------------------------------------------------------------------
// Scan a directory recursively for boundaries
// ---------------------------------------------------------------------------
function scanBoundaries(string $dir, int $depth = 0): void
{
    global $findings, $scanned, $passed, $root;

    $scanned++;

    if (!isImportantBoundary($dir)) {
        // Check subdirectories
        $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
        foreach ($subdirs ?: [] as $subdir) {
            scanBoundaries($subdir, $depth + 1);
        }
        return;
    }

    if (isExempt($dir)) {
        // Check subdirectories
        $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
        foreach ($subdirs ?: [] as $subdir) {
            scanBoundaries($subdir, $depth + 1);
        }
        return;
    }

    $relativePath = str_replace($root . '/', '', $dir);
    $hasReadme = false;
    $hasDictionary = false;
    $hasAdr = false;

    // Check README.md
    $readmePaths = [
        $dir . '/README.md',
        $dir . '/docs/README.md',
    ];

    foreach ($readmePaths as $readmePath) {
        if (file_exists($readmePath)) {
            $hasReadme = true;
            $readmeIssues = validateReadme($readmePath);
            if (!empty($readmeIssues)) {
                foreach ($readmeIssues as $issue) {
                    $findings[] = [
                        'severity' => 'MEDIUM',
                        'path' => str_replace($root . '/', '', $readmePath),
                        'finding' => $issue,
                    ];
                }
            }
            break;
        }
    }

    if (!$hasReadme) {
        $findings[] = [
            'severity' => 'HIGH',
            'path' => $relativePath,
            'finding' => 'Important boundary missing README.md — readers and AI cannot understand ownership',
        ];
    }

    // Check dictionary/
    $dictPaths = [
        $dir . '/docs/dictionary/',
        $dir . '/dictionary/',
    ];

    foreach ($dictPaths as $dictPath) {
        if (is_dir($dictPath)) {
            $hasDictionary = true;
            $mdFiles = glob($dictPath . '*.md');
            foreach ($mdFiles ?: [] as $dictFile) {
                $dictIssues = validateDictionaryEntry($dictFile);
                if (!empty($dictIssues)) {
                    foreach ($dictIssues as $issue) {
                        $findings[] = [
                            'severity' => 'MEDIUM',
                            'path' => str_replace($root . '/', '', $dictFile),
                            'finding' => $issue,
                        ];
                    }
                }
            }
            break;
        }
    }

    if (!$hasDictionary) {
        $findings[] = [
            'severity' => 'MEDIUM',
            'path' => $relativePath,
            'finding' => 'Important boundary missing dictionary/ — terms are not grounded for AI and new developers',
        ];
    }

    // Check adr/
    $adrPaths = [
        $dir . '/docs/adr/',
        $dir . '/adr/',
    ];

    foreach ($adrPaths as $adrPath) {
        if (is_dir($adrPath)) {
            $hasAdr = true;
            $mdFiles = glob($adrPath . '*.md');
            foreach ($mdFiles ?: [] as $adrFile) {
                $adrIssues = validateAdr($adrFile);
                if (!empty($adrIssues)) {
                    foreach ($adrIssues as $issue) {
                        $findings[] = [
                            'severity' => 'MEDIUM',
                            'path' => str_replace($root . '/', '', $adrFile),
                            'finding' => $issue,
                        ];
                    }
                }
            }
            break;
        }
    }

    // ADR is not required if no locked decisions exist (INFO only)
    if (!$hasAdr) {
        $findings[] = [
            'severity' => 'LOW',
            'path' => $relativePath,
            'finding' => 'Important boundary has no adr/ folder — create when architectural decisions are made',
        ];
    }

    $passed++;

    // Check subdirectories
    $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
    foreach ($subdirs ?: [] as $subdir) {
        scanBoundaries($subdir, $depth + 1);
    }
}

// ---------------------------------------------------------------------------
// Scan all markdown files for stale markers and orphan references
// ---------------------------------------------------------------------------
function scanDocumentationFiles(string $dir, string $root, array &$findings): void
{
    $mdFiles = collectMarkdownFiles($dir);

    foreach ($mdFiles as $mdFile) {
        $relativePath = str_replace($root . '/', '', $mdFile);

        // Skip third-party/vendor docs
        if (strpos($relativePath, 'vendor/') !== false) {
            continue;
        }

        // Check for stale markers
        $staleIssues = checkStaleDocMarkers($mdFile, $root);
        foreach ($staleIssues as $issue) {
            $findings[] = [
                'severity' => 'MEDIUM',
                'path' => $relativePath,
                'finding' => $issue,
            ];
        }

        // Check for orphan class references
        $orphanIssues = checkOrphanDocReferences($mdFile, $root);
        foreach ($orphanIssues as $issue) {
            $findings[] = [
                'severity' => 'LOW',
                'path' => $relativePath,
                'finding' => $issue,
            ];
        }

        // Validate Mermaid blocks
        $mermaidIssues = validateMermaidBlocks($mdFile, $root);
        foreach ($mermaidIssues as $issue) {
            $findings[] = [
                'severity' => 'MEDIUM',
                'path' => $relativePath,
                'finding' => $issue,
            ];
        }
    }
}

// ---------------------------------------------------------------------------
// Scan Flows directories for missing diagrams
// ---------------------------------------------------------------------------
function scanFlowDiagrams(string $dir, string $root, array &$findings): void
{
    if (!is_dir($dir)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir() && $item->getFilename() === 'Flows') {
            $flowDir = $item->getPathname();
            // Check individual flow subdirectories, not the parent Flows/ dir
            $subdirs = glob($flowDir . '/*', GLOB_ONLYDIR) ?: [];
            foreach ($subdirs as $subdir) {
                $diagramIssues = checkFlowDiagrams($subdir, $root);
                foreach ($diagramIssues as $issue) {
                    $findings[] = [
                        'severity' => 'LOW',
                        'path' => str_replace($root . '/', '', $subdir),
                        'finding' => $issue,
                    ];
                }
            }
        }
    }
}

// ---------------------------------------------------------------------------
// Main execution
// ---------------------------------------------------------------------------
echo "=== Self-Explaining Architecture Check ===\n";
echo "Scanning: $componentsDir\n";
echo "Docs scan: $docsDir\n\n";

if (!is_dir($componentsDir)) {
    echo "RED: components/ directory not found\n";
    exit(1);
}

// Collect all PHP classes first for cross-reference checks
collectClassFiles($componentsDir);
if (is_dir($root . '/framework')) {
    collectClassFiles($root . '/framework');
}
echo "Class index: " . count($allClassFiles) . " files, " . count($allClassNames) . " classes indexed\n\n";

// Scan component boundaries
$componentDirs = glob($componentsDir . '/*', GLOB_ONLYDIR);
foreach ($componentDirs ?: [] as $componentDir) {
    scanBoundaries($componentDir);
}

// Scan all documentation files for stale markers and orphan references
echo "Scanning documentation files...\n";
if (is_dir($docsDir)) {
    scanDocumentationFiles($docsDir, $root, $findings);
}
// Also scan component-local docs
foreach ($componentDirs ?: [] as $componentDir) {
    $componentDocsDir = $componentDir . '/docs';
    if (is_dir($componentDocsDir)) {
        scanDocumentationFiles($componentDocsDir, $root, $findings);
    }
}

// Scan Flows directories for missing diagrams
echo "Scanning flow directories for diagram coverage...\n";
scanFlowDiagrams($componentsDir, $root, $findings);

// Print findings
$blockerCount = 0;
$highCount = 0;
$mediumCount = 0;
$lowCount = 0;

foreach ($findings as $f) {
    switch ($f['severity']) {
        case 'BLOCKER': $blockerCount++; break;
        case 'HIGH': $highCount++; break;
        case 'MEDIUM': $mediumCount++; break;
        case 'LOW': $lowCount++; break;
    }
}

$toolName = 'self-explaining-architecture';

if ($writeBaselinePath !== null) {
    $target = $writeBaselinePath === '1' ? $baselinePath : $root . '/' . ltrim($writeBaselinePath, '/');
    avax_gate_write_baseline($target, $toolName, $findings, $root);
    echo "BASELINE_WRITTEN {$target}\n";
    echo "Entries: " . count($findings) . "\n";
    exit(0);
}

if ($mode === 'baseline') {
    $result = avax_gate_compare_with_baseline($toolName, $findings, $baselinePath, $root);
    avax_gate_print_baseline_result($toolName, $result);
    exit($result['valid'] ? 0 : 1);
}

if ($mode === 'changed') {
    $changedFiles = avax_gate_changed_files($root);
    $changedFindings = avax_gate_filter_changed_findings($findings, $changedFiles);
    $valid = avax_gate_print_changed_result($toolName, $changedFiles, $changedFindings, false);
    exit($valid ? 0 : 1);
}

if (empty($findings)) {
    echo "GREEN — All scanned boundaries have required documentation.\n";
    echo "Boundaries scanned: $scanned, Important boundaries: $passed\n";
    exit(0);
}

echo "=== FINDINGS ===\n\n";

$severityOrder = ['BLOCKER', 'HIGH', 'MEDIUM', 'LOW'];
foreach ($severityOrder as $severity) {
    $severityFindings = array_filter($findings, fn($f) => $f['severity'] === $severity);
    if (empty($severityFindings)) {
        continue;
    }

    echo "--- $severity (" . count($severityFindings) . ") ---\n";
    foreach ($severityFindings as $f) {
        echo "  [$severity] {$f['path']}\n";
        echo "    {$f['finding']}\n\n";
    }
}

echo "=== SUMMARY ===\n";
echo "Boundaries scanned: $scanned\n";
echo "Important boundaries: $passed\n";
echo "BLOCKER: $blockerCount\n";
echo "HIGH: $highCount\n";
echo "MEDIUM: $mediumCount\n";
echo "LOW: $lowCount\n";
echo "Total findings: " . count($findings) . "\n";

exit(count($findings) > 0 ? 1 : 0);
