<?php

$backupFile = __DIR__ . '/../avax-backup.txt';
$auditFile  = __DIR__ . '/audit_broken_refs.php';

// We will parse the output of audit_broken_refs to get missing classes
exec('php ' . $auditFile . ' 2>&1 | grep "\[CRITICAL\]"', $output);

$missingClasses = [];
foreach ($output as $line) {
    if (preg_match('/MISSING: (.*)  \[CRITICAL\]/', $line, $matches)) {
        $missingClasses[] = trim($matches[1]);
    }
}

echo "Found " . count($missingClasses) . " critical missing classes.\n";

$backupContent = file_get_contents($backupFile);

$restored = 0;
foreach ($missingClasses as $classFqn) {
    $parts     = explode('\\', $classFqn);
    $className = end($parts);

    // Attempt to find the class block in the backup file
    // Look for: class ClassName
    $pattern = '/(?:final\s+|abstract\s+|readonly\s+)*class\s+' . $className . '\b[^{]*\{.*?(?=\n(?:final\s+|abstract\s+|readonly\s+)*(?:class|interface|trait|enum)\s|\z)/s';
    if (preg_match($pattern, $backupContent, $matches)) {
        echo "Found $className in backup!\n";
    }
}
