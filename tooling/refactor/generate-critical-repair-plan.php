<?php

$lines = file('Code-Review-And-ToDo/v1-integrity/broken-reference-groups.md', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$criticalRefs = [];
$currentCategory = '';
$currentSymbol = '';

foreach ($lines as $line) {
    if (preg_match('/^## (.*?) \(/', $line, $matches)) {
        $currentCategory = trim($matches[1]);
        continue;
    }
    
    // Ignore test-only references for now as per instructions (fix production first).
    // Actually the instruction says "Repair or explicitly classify all CRITICAL broken internal references".
    // We should parse all CRITICAL refs.
    
    if (preg_match('/^- \*\*(.*?)\*\* \[(.*?)\]/', $line, $matches)) {
        $symbol = $matches[1];
        $severity = $matches[2];
        if ($severity === 'CRITICAL') {
            $currentSymbol = $symbol;
            if (!isset($criticalRefs[$currentSymbol])) {
                $criticalRefs[$currentSymbol] = [
                    'count' => 0,
                    'usages' => [],
                    'category' => $currentCategory
                ];
            }
        } else {
            $currentSymbol = '';
        }
    } elseif ($currentSymbol !== '' && preg_match('/^\s+- (.*?):(\d+) \((.*?)\)/', $line, $matches)) {
        $file = $matches[1];
        $criticalRefs[$currentSymbol]['usages'][] = $file;
        $criticalRefs[$currentSymbol]['count']++;
    }
}

// Rank by count descending
uasort($criticalRefs, function($a, $b) {
    return $b['count'] <=> $a['count'];
});

$markdown = "# Critical Broken Reference Repair Plan\n\n";
$markdown .= "| Missing symbol | Count | Referenced from (sample) | Likely canonical target | Fix type | Risk | Status |\n";
$markdown .= "|---|---|---|---|---|---|---|\n";

foreach ($criticalRefs as $symbol => $data) {
    $count = $data['count'];
    $sample = count($data['usages']) > 0 ? dirname($data['usages'][0]) : 'unknown';
    $cat = strtolower($data['category']);
    
    $fixType = 'requires-human-decision';
    if ($cat === 'test-only') {
        $fixType = 'test-only-postpone';
    } elseif ($cat === 'docs-only') {
        $fixType = 'docs-only-ignore';
    } elseif ($cat === 'vendor-external') {
        $fixType = 'false-positive';
    }
    
    $canonical = '?';
    if (str_contains($symbol, 'Avax\DataLayer')) {
        $canonical = str_replace('Avax\DataLayer', 'Avax\Components\DataStack\Database', $symbol);
        if ($fixType === 'requires-human-decision') $fixType = 'canonical-import-rewrite';
    } elseif (str_contains($symbol, 'Avax\DataFoundation')) {
        $canonical = str_replace('Avax\DataFoundation', 'Avax\Components\DataStack\Database', $symbol);
        if ($fixType === 'requires-human-decision') $fixType = 'canonical-import-rewrite';
    } elseif (str_contains($symbol, 'Avax\Components\Application\Container\DI')) {
        // e.g. Avax\Components\Application\Container\DI\Capabilities -> Avax\Components\Application\Container\System\Capabilities
        $canonical = str_replace('\DI\\', '\System\\', $symbol);
        if ($fixType === 'requires-human-decision') $fixType = 'canonical-import-rewrite';
    } elseif (str_contains($symbol, 'Avax\Components\Application\Container\DependencyInjection')) {
        $canonical = str_replace('\DependencyInjection\\', '\System\\', $symbol);
        if ($fixType === 'requires-human-decision') $fixType = 'canonical-import-rewrite';
    } elseif (str_contains($symbol, 'Avax\System\Configuration')) {
        $canonical = str_replace('Avax\System\Configuration', 'Avax\Framework\System\Configuration', $symbol);
        if ($fixType === 'requires-human-decision') $fixType = 'canonical-import-rewrite';
    } elseif (str_contains($symbol, 'Avax\HTTP')) {
        $canonical = str_replace('Avax\HTTP', 'Avax\Components\HTTP', $symbol);
        if ($fixType === 'requires-human-decision') $fixType = 'canonical-import-rewrite';
    } elseif (str_contains($symbol, 'Avax\Session')) {
        $canonical = str_replace('Avax\Session', 'Avax\Components\HTTP\Session', $symbol);
        if ($fixType === 'requires-human-decision') $fixType = 'canonical-import-rewrite';
    } elseif (str_contains($symbol, 'Avax\Filesystem')) {
        $canonical = str_replace('Avax\Filesystem', 'Avax\Components\Operations\Filesystem', $symbol);
        if ($fixType === 'requires-human-decision') $fixType = 'canonical-import-rewrite';
    }

    $markdown .= "| `$symbol` | $count | `$sample` | `$canonical` | $fixType | Low | PENDING |\n";
}

file_put_contents('Code-Review-And-ToDo/v1-integrity/critical-broken-reference-repair-plan.md', $markdown);
echo "Plan created with " . count($criticalRefs) . " critical references.\n";
