<?php

$phpstanCmd = 'vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress';
shell_exec("$phpstanCmd > Code-Review-And-ToDo/v1-integrity/phpstan-full-raw.txt 2>&1 || true");

$lines = file('Code-Review-And-ToDo/v1-integrity/phpstan-full-raw.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$components = [];
$errorTypes = [];

foreach ($lines as $line) {
    if (!str_contains($line, ':')) continue;
    $parts = explode(':', $line, 3);
    if (count($parts) < 3) continue;
    
    $file = $parts[0];
    $message = trim($parts[2]);
    
    // Skip if it's not an absolute path or doesn't look like an error line
    if (!str_starts_with($file, '/') && !str_starts_with($file, 'C:\\')) {
        continue;
    }
    
    // Determine component
    $relFile = str_replace(getcwd() . '/', '', $file);
    $component = 'Other';
    if (str_starts_with($relFile, 'components/')) {
        $cParts = explode('/', $relFile);
        if (count($cParts) > 2) {
            $component = 'components/' . $cParts[1] . '/' . $cParts[2];
        }
    } elseif (str_starts_with($relFile, 'framework/System/')) {
        $component = 'framework/System';
    } elseif (str_starts_with($relFile, 'tests/')) {
        $component = 'tests';
    }

    // Determine error family
    $family = 'Other';
    if (str_contains($message, 'not found')) $family = 'unknown class/method/property';
    elseif (str_contains($message, 'Call to an undefined method')) $family = 'unknown method';
    elseif (str_contains($message, 'Access to an undefined property')) $family = 'unknown property';
    elseif (str_contains($message, 'does not specify its types')) $family = 'generic/PHPDoc drift';
    elseif (str_contains($message, 'Missing parameter')) $family = 'wrong constructor/method call';
    elseif (str_contains($message, 'Unknown parameter')) $family = 'wrong named argument';
    elseif (str_contains($message, 'should return')) $family = 'wrong return type';
    elseif (str_contains($message, 'expects')) $family = 'wrong parameter type';
    elseif (str_contains($message, 'null')) $family = 'nullable mismatch';
    
    $components[$component][$family] = ($components[$component][$family] ?? 0) + 1;
    $errorTypes[$family] = ($errorTypes[$family] ?? 0) + 1;
}

arsort($errorTypes);

$markdown = "# PHPStan Error Groups\n\n";
$markdown .= "## Top Error Families\n\n";
foreach (array_slice($errorTypes, 0, 20) as $family => $count) {
    $markdown .= "- **$family**: $count\n";
}

$markdown .= "\n## Errors by Component\n\n";
ksort($components);
foreach ($components as $comp => $families) {
    $markdown .= "### $comp\n\n";
    arsort($families);
    foreach ($families as $family => $count) {
        $markdown .= "- $family: $count\n";
    }
    $markdown .= "\n";
}

file_put_contents('Code-Review-And-ToDo/v1-integrity/phpstan-error-groups.md', $markdown);
echo "Categorization complete.\n";
