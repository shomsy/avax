<?php

$auditFile = __DIR__ . '/audit_broken_refs.php';
exec('php ' . escapeshellarg($auditFile) . ' 2>&1 | grep "\[CRITICAL\]"', $output);

$missingClasses = [];
foreach ($output as $line) {
    if (preg_match('/MISSING: (.*)  \[CRITICAL\]/', $line, $matches)) {
        $missingClasses[] = trim($matches[1]);
    }
}

echo "Generating stubs for " . count($missingClasses) . " critical missing classes...\n";

foreach ($missingClasses as $classFqn) {
    // Only generate for Avax namespace
    if (! str_starts_with($classFqn, 'Avax\\')) {
        continue;
    }

    $parts = explode('\\', $classFqn);
    array_shift($parts); // Remove 'Avax'

    $baseDir  = null;
    $topLevel = array_shift($parts);

    if ($topLevel === 'Components') {
        $baseDir = __DIR__ . '/../components/';
    } elseif ($topLevel === 'Framework') {
        $baseDir = __DIR__ . '/../framework/';
    } else {
        continue; // e.g. DataLayer, DataHandling, skip those
    }

    $className = array_pop($parts);
    $path      = $baseDir . implode('/', $parts);
    $file      = $path . '/' . $className . '.php';

    if (! is_dir($path)) {
        mkdir($path, 0777, true);
    }

    $namespace = "Avax\\" . $topLevel;
    if (count($parts) > 0) {
        $namespace .= "\\" . implode('\\', $parts);
    }

    // Guess type (interface, trait, class)
    $type = 'class';
    if (str_ends_with($className, 'Interface') || str_starts_with($className, 'Contracts')) {
        $type = 'interface';
    } elseif (str_ends_with($className, 'Trait')) {
        $type = 'trait';
    }

    $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace $namespace;\n\n$type $className\n{\n}\n";

    if (! file_exists($file)) {
        file_put_contents($file, $content);
        echo "Created $file\n";
    }
}
