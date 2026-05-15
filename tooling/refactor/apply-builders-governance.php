<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$directories = [$projectRoot . '/components', $projectRoot . '/framework'];

$filesToMove = [];

function findFiles(string $dir, array &$filesToMove) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            findFiles($path, $filesToMove);
        } elseif (is_file($path) && str_ends_with($path, '.php')) {
            // Check if it's inside a Configuration directory but NOT already inside Builders
            if (str_contains($path, '/System/Configuration/') && !str_contains($path, '/System/Configuration/Builders/')) {
                // Must be Build*, Register*, Assemble*, or *Builder.php
                $basename = basename($path);
                if (
                    str_starts_with($basename, 'Build') || 
                    str_starts_with($basename, 'Register') || 
                    str_starts_with($basename, 'Assemble') || 
                    str_ends_with($basename, 'Builder.php')
                ) {
                    $filesToMove[] = $path;
                }
            }
            
            // Also check if Configuration is wrongfully inside Capabilities
            if (str_contains($path, '/System/Capabilities/') && str_contains($path, '/Configuration/')) {
                $basename = basename($path);
                if (
                    str_starts_with($basename, 'Build') || 
                    str_starts_with($basename, 'Register') || 
                    str_starts_with($basename, 'Assemble') || 
                    str_ends_with($basename, 'Builder.php')
                ) {
                    $filesToMove[] = $path;
                }
            }
        }
    }
}

foreach ($directories as $dir) {
    findFiles($dir, $filesToMove);
}

// Map old namespace to new namespace
$namespaceUpdates = [];

foreach ($filesToMove as $oldPath) {
    // Determine the new path
    if (str_contains($oldPath, '/System/Capabilities/') && str_contains($oldPath, '/Configuration/')) {
        // e.g. framework/System/Capabilities/FailureBoundary/Configuration/BuildFailureBoundary.php
        // -> framework/System/Capabilities/FailureBoundary/Configuration/Builders/BuildFailureBoundary.php
        $newPath = str_replace('/Configuration/', '/Configuration/Builders/', $oldPath);
    } else {
        // e.g. components/HTTP/Router/System/Configuration/RouterBuilder.php
        // -> components/HTTP/Router/System/Configuration/Builders/RouterBuilder.php
        $newDir = dirname($oldPath) . '/Builders';
        $newPath = $newDir . '/' . basename($oldPath);
    }
    
    // Read old namespace
    $content = file_get_contents($oldPath);
    if (preg_match('/namespace\s+([a-zA-Z0-9_\\\\]+);/', $content, $matches)) {
        $oldNamespace = $matches[1];
        $className = basename($oldPath, '.php');
        $oldFqn = $oldNamespace . '\\' . $className;
        
        $newNamespace = $oldNamespace . '\\Builders';
        $newFqn = $newNamespace . '\\' . $className;
        
        $namespaceUpdates[$oldFqn] = $newFqn;
        $namespaceUpdates[$oldNamespace] = $newNamespace;
        
        // Update namespace in file content
        $newContent = preg_replace('/namespace\s+' . preg_quote($oldNamespace, '/') . ';/', 'namespace ' . $newNamespace . ';', $content);
        
        // Ensure Builders dir exists
        $newDir = dirname($newPath);
        if (!is_dir($newDir)) {
            mkdir($newDir, 0777, true);
        }
        
        file_put_contents($newPath, $newContent);
        unlink($oldPath);
        
        echo "Moved $oldPath -> $newPath\n";
    }
}

echo "\nUpdating uses across all files...\n";

// Now find all PHP files and update uses
function updateUses(string $dir, array $updates) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            updateUses($path, $updates);
        } elseif (is_file($path) && str_ends_with($path, '.php')) {
            $content = file_get_contents($path);
            $modified = false;
            
            foreach ($updates as $oldFqn => $newFqn) {
                // Ignore the namespace mappings for this regex, only full class names
                if (!str_contains($oldFqn, '\\Builders') && class_exists_in_updates($oldFqn)) {
                    // Replace use statements
                    if (str_contains($content, 'use ' . $oldFqn . ';')) {
                        $content = str_replace('use ' . $oldFqn . ';', 'use ' . $newFqn . ';', $content);
                        $modified = true;
                    }
                    
                    // Replace inline fully qualified names
                    if (str_contains($content, '\\' . $oldFqn)) {
                        $content = str_replace('\\' . $oldFqn, '\\' . $newFqn, $content);
                        $modified = true;
                    }
                }
            }
            
            if ($modified) {
                file_put_contents($path, $content);
            }
        }
    }
}

function class_exists_in_updates($key) {
    // We stored both old namespace and old FQN in the array. FQNs end with a class name.
    return preg_match('/\\\\[A-Z][a-zA-Z0-9_]*$/', $key);
}

foreach ($directories as $dir) {
    updateUses($dir, $namespaceUpdates);
}
updateUses($projectRoot . '/tests', $namespaceUpdates);

echo "Done.\n";
