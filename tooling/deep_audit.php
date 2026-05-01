<?php

declare(strict_types=1);

/**
 * Deep Feature Audit: avax.txt vs components/
 *
 * Extracts every class, interface, trait and enum declaration from avax.txt,
 * then checks which ones exist in the current components/ directory.
 */
$avaxFile      = '/home/shomsy/projects/avax/avax.txt';
$componentsDir = '/home/shomsy/projects/avax/components';

// ─── Step 1: Extract all declarations from avax.txt ───
echo "Step 1: Scanning avax.txt for all class/interface/trait/enum declarations...\n";

$oldDeclarations = [];
$currentFile     = '';
$handle          = fopen($avaxFile, 'r');
if (! $handle) {
    exit("Cannot open avax.txt\n");
}

while (($line = fgets($handle)) !== false) {
    $line = rtrim($line);

    // Track which file we're in
    if (str_starts_with($line, '=== components/') && str_ends_with($line, ' ===')) {
        $currentFile = substr($line, 4, -4); // "components/Foo/Bar.php"

        continue;
    }

    // Skip non-component files
    if (! str_starts_with($currentFile, 'components/')) {
        continue;
    }

    // Match class/interface/trait/enum declarations
    if (preg_match('/^(?:final\s+)?(?:readonly\s+)?(?:abstract\s+)?(class|interface|trait|enum)\s+(\w+)/', $line, $m)) {
        $type = $m[1];
        $name = $m[2];

        // Extract the component (top-level folder under components/)
        $parts     = explode('/', $currentFile);
        $component = $parts[1] ?? 'unknown';

        // Get the subpath for context
        $subpath = implode('/', array_slice($parts, 2));

        $key = $component . '::' . $name;
        if (! isset($oldDeclarations[$key])) {
            $oldDeclarations[$key] = [
                'type'      => $type,
                'name'      => $name,
                'component' => $component,
                'file'      => $currentFile,
                'subpath'   => $subpath,
            ];
        }
    }
}
fclose($handle);

echo '  Found ' . count($oldDeclarations) . " unique declarations in avax.txt\n\n";

// ─── Step 2: Extract all declarations from current components/ ───
echo "Step 2: Scanning current components/ directory...\n";

$newDeclarations = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($componentsDir, RecursiveDirectoryIterator::SKIP_DOTS),
);

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $content      = file_get_contents($file->getPathname());
    $relativePath = str_replace('/home/shomsy/projects/avax/', '', $file->getPathname());

    // Extract component name
    $parts     = explode('/', $relativePath);
    $component = $parts[1] ?? 'unknown';

    // Also check if it's inside a suite (Application/Container, DataStack/Data, etc.)
    $suiteComponent = $component;
    if (isset($parts[2]) && is_dir($componentsDir . '/' . $component . '/' . $parts[2] . '/System')) {
        $suiteComponent = $parts[2]; // The actual component inside the suite
    }

    if (preg_match_all('/^(?:final\s+)?(?:readonly\s+)?(?:abstract\s+)?(class|interface|trait|enum)\s+(\w+)/m', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $type = $m[1];
            $name = $m[2];

            // Register under both the direct component and the suite-child
            $newDeclarations[$name] = [
                'type'       => $type,
                'name'       => $name,
                'component'  => $component,
                'suiteChild' => $suiteComponent,
                'file'       => $relativePath,
            ];
        }
    }
}

echo '  Found ' . count($newDeclarations) . " unique declarations in components/\n\n";

// ─── Step 3: Compare ───
echo "Step 3: Comparing...\n\n";

$missing = [];
$found   = [];

foreach ($oldDeclarations as $key => $old) {
    $name = $old['name'];

    // Skip test support classes, documentation artifacts, config files
    if (str_contains($old['file'], '/tests/')
        || str_contains($old['file'], '/.agents/')
        || str_contains($old['file'], '/Code-Review-And-ToDo/')
        || str_contains($old['file'], '/docs/')
        || str_contains($old['file'], '/tooling/')
        || str_ends_with($old['file'], '.md')
        || str_ends_with($old['file'], '.txt')
        || str_ends_with($old['file'], '.sh')
        || str_ends_with($old['file'], '.json')
    ) {
        continue;
    }

    if (isset($newDeclarations[$name])) {
        $found[$key] = $old;
    } else {
        $missing[$key] = $old;
    }
}

echo '  ✅ Found in new code: ' . count($found) . "\n";
echo '  ❌ Missing from new code: ' . count($missing) . "\n\n";

// ─── Step 4: Group missing by component ───
$byComponent = [];
foreach ($missing as $item) {
    $comp = $item['component'];
    if (! isset($byComponent[$comp])) {
        $byComponent[$comp] = [];
    }
    $byComponent[$comp][] = $item;
}

ksort($byComponent);

// ─── Step 5: Generate report ───
$report = "# 🔬 Deep Feature Audit Report\n\n";
$report .= '**Generated:** ' . date('Y-m-d H:i:s') . "\n\n";
$report .= 'This report was generated by scanning every `class`, `interface`, `trait` and `enum` declaration ';
$report .= "in `avax.txt` (stari kod) and comparing it against the current `components/` folder (novi kod).\n\n";
$report .= "**Statistika:**\n";
$report .= '- Ukupno deklaracija u `avax.txt`: **' . count($oldDeclarations) . "**\n";
$report .= '- Ukupno deklaracija u `components/`: **' . count($newDeclarations) . "**\n";
$report .= '- ✅ Pronađeno u novom kodu: **' . count($found) . "**\n";
$report .= '- ❌ Nedostaje u novom kodu: **' . count($missing) . "**\n\n";
$report .= "---\n\n";

foreach ($byComponent as $comp => $items) {
    $report .= "## 📦 {$comp}\n\n";
    $report .= "| Tip | Ime klase | Originalni fajl |\n";
    $report .= "|-----|-----------|------------------|\n";

    foreach ($items as $item) {
        $report .= "| `{$item['type']}` | **{$item['name']}** | `{$item['subpath']}` |\n";
    }
    $report .= "\n";
}

$outputPath = '/home/shomsy/projects/avax/deep-feature-audit.md';
file_put_contents($outputPath, $report);

echo "✅ Report saved to: {$outputPath}\n";
echo '   Total missing classes/interfaces: ' . count($missing) . "\n";
