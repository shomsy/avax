<?php

$log = file_get_contents('Code-Review-And-ToDo/v1-integrity/autoload-skip-report.txt');
preg_match_all('/Class (.*?) located in \.\/(.*?) does not comply/', $log, $matches);

$skips = [];
for ($i = 0; $i < count($matches[0]); $i++) {
    $class = $matches[1][$i];
    $file = $matches[2][$i];
    $skips[] = ['class' => $class, 'file' => $file];
}

foreach ($skips as $skip) {
    $file = $skip['file'];
    $class = $skip['class'];
    if (!file_exists($file)) continue;

    $content = file_get_contents($file);
    
    // Expected PSR-4 Class Name
    $expectedClass = 'Avax\\Components\\' . str_replace('/', '\\', substr($file, 11, -4));
    
    echo "File: $file\n";
    echo "  Actual: $class\n";
    echo "  Expect: $expectedClass\n";
    
    // Let's try to fix namespace
    $parts = explode('\\', $expectedClass);
    $expectedClassName = array_pop($parts);
    $expectedNamespace = implode('\\', $parts);
    
    // What is the current namespace?
    if (preg_match('/namespace\s+(.*?);/', $content, $nsMatch)) {
        $currentNamespace = trim($nsMatch[1]);
        if ($currentNamespace !== $expectedNamespace) {
            echo "  Fixing namespace: $currentNamespace -> $expectedNamespace\n";
            $content = str_replace("namespace $currentNamespace;", "namespace $expectedNamespace;", $content);
        }
    }
    
    // What is the current class name?
    if (preg_match('/class\s+([A-Za-z0-9_]+)/', $content, $clMatch)) {
        $currentClassName = $clMatch[1];
        if ($currentClassName !== $expectedClassName) {
            echo "  Fixing class name: $currentClassName -> $expectedClassName\n";
            $content = str_replace("class $currentClassName", "class $expectedClassName", $content);
        }
    }

    // Write back
    file_put_contents($file, $content);
}
