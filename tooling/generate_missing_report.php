<?php

declare(strict_types=1);

$avaxTxt              = '/home/shomsy/projects/avax/avax.txt';
$currentComponentsDir = '/home/shomsy/projects/avax/components';

if (! file_exists($avaxTxt)) {
    exit("avax.txt not found.\n");
}

$oldComponents = [];
$handle        = fopen($avaxTxt, 'r');
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        if (str_starts_with($line, '=== components/')) {
            $path  = trim(substr($line, 15, -4)); // extract path after "=== components/"
            $parts = explode('/', $path);
            if (count($parts) >= 1) {
                $component = $parts[0];
                if (! isset($oldComponents[$component])) {
                    $oldComponents[$component] = [];
                }

                if (count($parts) >= 2 && ! in_array($parts[1], $oldComponents[$component], true)) {
                    $oldComponents[$component][] = $parts[1];
                }
            }
        }
    }

    fclose($handle);
}

$newComponents = [];
$dirs          = glob($currentComponentsDir . '/*', GLOB_ONLYDIR);
foreach ($dirs as $dir) {
    $comp                 = basename($dir);
    $newComponents[$comp] = [];
    $subDirs              = glob($dir . '/*', GLOB_ONLYDIR);
    foreach ($subDirs as $subDir) {
        $newComponents[$comp][] = basename($subDir);
    }
}

$report = "# Missing Features Report (Old vs New Architecture)\n\n";
$report .= "This report compares the components backed up in `avax.txt` with the current `components/` directory.\n\n";

$missingSuites = [];
foreach (array_keys($oldComponents) as $oldComp) {
    if (! isset($newComponents[$oldComp])) {
        // Wait, maybe it was moved inside a suite?
        $foundInSuite   = false;
        $foundSuiteName = '';
        foreach ($newComponents as $suite => $subComps) {
            if (in_array($oldComp, $subComps, true)) {
                $foundInSuite   = true;
                $foundSuiteName = $suite;

                break;
            }
        }

        if (! $foundInSuite) {
            $missingSuites[] = $oldComp;
        }
    }
}

if ($missingSuites !== []) {
    $report .= "## Completely Missing Components / Suites\n";
    foreach ($missingSuites as $missingSuite) {
        $report .= "- **{$missingSuite}** (Found in avax.txt, not found in new architecture)\n";
    }

    $report .= "\n";
}

$report .= "## Detailed Component Analysis\n";
foreach ($oldComponents as $oldComp => $oldSubComps) {
    $report                      .= sprintf('### %s%s', $oldComp, PHP_EOL);

    // Check if it's a suite now or exists at root
    $currentLocation = null;
    if (isset($newComponents[$oldComp])) {
        $currentLocation = $newComponents[$oldComp];
    } else {
        foreach ($newComponents as $suite => $subComps) {
            if (in_array($oldComp, $subComps, true)) {
                // We found it inside a suite
                $currentLocation = glob($currentComponentsDir . '/' . $suite . '/' . $oldComp . '/*', GLOB_ONLYDIR);
                $currentLocation = array_map(basename(...), $currentLocation);

                break;
            }
        }
    }

    if ($currentLocation === null) {
        $report .= "- *Component is entirely missing.*\n";
        // List top 5 subcomponents/files that are lost
        $preview = array_slice($oldSubComps, 0, 5);
        if ($preview !== []) {
            $report .= '- Lost features include: `' . implode('`, `', $preview) . "`\n";
        }
    } else {
        $missingSub = array_diff($oldSubComps, $currentLocation);
        // Filter out typical files like .php that might have been caught
        $missingSub = array_filter($missingSub, static fn (string $s) : bool => ! str_ends_with($s, '.php'));

        if ($missingSub === []) {
            $report .= "- *Fully migrated or structure matched.*\n";
        } else {
            $report .= "- **Missing sub-components/folders:**\n";
            foreach ($missingSub as $sub) {
                $report .= "  - `{$sub}`\n";
            }
        }
    }

    $report .= "\n";
}

file_put_contents('/home/shomsy/projects/avax/Missing-Features-Report.md', $report);
echo "Report generated at /home/shomsy/projects/avax/Missing-Features-Report.md\n";
