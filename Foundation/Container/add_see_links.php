<?php

$files = [
    'Features/Core/Contracts/InjectorInterface.php',
    'Features/Core/Contracts/RegistryInterface.php',
    'Features/Core/Contracts/ResolverInterface.php',
    'Features/Core/DTO/ErrorDTO.php',
    'Features/Core/DTO/InjectionReport.php',
    'Features/Core/DTO/SuccessDTO.php',
    'Features/Core/Exceptions/ContainerException.php',
    'Features/Core/Exceptions/ContainerExceptionInterface.php',
    'Features/Core/Exceptions/ResolutionException.php',
    'Features/Core/Exceptions/ServiceNotFoundException.php',
    'Features/Core/Utils/ArrayTools.php',
    'Features/Core/Utils/StrTools.php',
    'Features/Think/Cache/NullPrototypeCache.php',
];

foreach ($files as $file) {
    $fullPath = __DIR__ . '/' . $file;

    if (!file_exists($fullPath)) {
        echo "Skipping (not found): $file\n";
        continue;
    }

    $content = file_get_contents($fullPath);

    // Extract namespace and class/interface/trait name
    preg_match('/namespace\s+([^;]+);/', $content, $nsMatch);
    preg_match('/(class|interface|trait)\s+(\w+)/', $content, $classMatch);

    if (!$nsMatch || !$classMatch) {
        echo "Skipping (no class found): $file\n";
        continue;
    }

    $namespace = $nsMatch[1];
    $type = $classMatch[1];
    $className = $classMatch[2];

    // Generate docs path from file path
    $docsPath = 'docs/' . str_replace('.php', '.md', $file);

    // Check if @see already exists
    if (strpos($content, '@see docs/') !== false) {
        echo "Skipping (already has @see): $file\n";
        continue;
    }

    // Find the class/interface/trait declaration
    $pattern = '/(' . preg_quote($type, '/') . '\s+' . preg_quote($className, '/') . ')/';

    // Check if there's already a docblock before the declaration
    if (preg_match('/\/\*\*.*?\*\/\s*' . $pattern . '/s', $content)) {
        // Add @see to existing docblock
        $content = preg_replace(
            '/(\/\*\*.*?)(\s*\*\/\s*)(' . $pattern . ')/s',
            '$1' . "\n * @see " . $docsPath . '$2$3',
            $content
        );
    } else {
        // Add new docblock
        $docblock = "/**\n * @see $docsPath\n */\n";
        $content = preg_replace(
            '/(' . $pattern . ')/',
            $docblock . '$1',
            $content,
            1
        );
    }

    file_put_contents($fullPath, $content);
    echo "Updated: $file\n";
}

echo "\nDone!\n";
