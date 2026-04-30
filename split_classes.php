<?php

$files = [
    'components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheFreshness.php',
    'components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifest.php',
    'components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/CheckCompiledCacheIsFresh.php',
    'components/Application/Cache/System/Capabilities/Source/ControlConsistency/EventualConsistency.php',
    'components/Application/Cache/System/Capabilities/Stores/RedisCacheStore.php',
    'components/Application/Cache/System/Capabilities/Distribution/ReplicateCachedValues/ReplicationPolicy.php',
    'components/Application/Cache/System/Capabilities/Distribution/ReplicateCachedValues/CacheReplication.php',
    'components/Application/Cache/System/Capabilities/Distribution/CacheNodeHealth.php',
    'components/Application/Cache/System/Flows/Protection/ProtectCacheSource/AcquireCacheStampedeLock.php',
    'components/Application/Cache/System/Flows/Compiled/WarmCompiledCache/WarmCompiledCache.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;

    $content = file_get_contents($file);
    
    // Extract namespace
    preg_match('/namespace\s+([^;]+);/', $content, $nsMatch);
    $namespace = $nsMatch[0] ?? '';
    
    // Extract strictly top-level use statements (naive approach, handles simple uses)
    preg_match_all('/^use\s+[^;]+;/m', $content, $useMatches);
    $uses = implode("\n", $useMatches[0]);

    // Match top-level class, interface, trait, or enum definitions
    // This regex looks for start of line or spaces, then final/readonly/abstract modifiers, then the keyword and name, until the matching closing brace.
    // Because matching balanced braces with regex in PHP is complex, we will use a simpler approach:
    // Split the file by `^(?:final\s+|readonly\s+|abstract\s+)*class\s+` or `enum` or `interface`
    // Actually, splitting by regex for classes is hard. Let's use tokens to find the start and end of each declaration.
    
    $tokens = token_get_all($content);
    $declarations = [];
    $currentDecl = null;
    $braceLevel = 0;
    
    for ($i = 0; $i < count($tokens); $i++) {
        $token = $tokens[$i];
        
        if (is_array($token) && in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM])) {
            // Check if it's a real declaration
            $isDecl = true;
            for ($j = $i - 1; $j >= 0; $j--) {
                if (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) continue;
                if (is_array($tokens[$j]) && in_array($tokens[$j][0], [T_DOUBLE_COLON, T_NEW])) $isDecl = false;
                break;
            }
            
            if ($isDecl && $braceLevel === 0) {
                // Find name
                $name = 'Unknown';
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                        $name = $tokens[$j][1];
                        break;
                    }
                }
                
                // Rewind to capture docblocks, attributes, and modifiers (final, readonly, etc)
                $startIndex = $i;
                for ($j = $i - 1; $j >= 0; $j--) {
                    if (is_array($tokens[$j]) && in_array($tokens[$j][0], [T_WHITESPACE, T_FINAL, T_ABSTRACT, T_READONLY, T_DOC_COMMENT, T_ATTRIBUTE])) {
                        $startIndex = $j;
                    } else if (is_string($tokens[$j]) && in_array($tokens[$j], ['[', ']'])) {
                        $startIndex = $j; // for attributes #[Attr]
                    } else {
                        break;
                    }
                }
                
                $currentDecl = [
                    'name' => $name,
                    'start' => $startIndex,
                    'braceStart' => -1,
                    'end' => -1
                ];
            }
        }
        
        if ($currentDecl !== null) {
            $char = is_array($token) ? $token[1] : $token;
            if ($char === '{') {
                if ($currentDecl['braceStart'] === -1) $currentDecl['braceStart'] = $i;
                $braceLevel++;
            } elseif ($char === '}') {
                $braceLevel--;
                if ($braceLevel === 0) {
                    $currentDecl['end'] = $i;
                    $declarations[] = $currentDecl;
                    $currentDecl = null;
                }
            }
        }
    }
    
    if (count($declarations) > 1) {
        $baseName = pathinfo($file, PATHINFO_FILENAME);
        $dir = dirname($file);
        
        // The first declaration stays in the original file, unless its name matches the filename perfectly
        // Actually, let's just rewrite the original file with the declaration that matches the filename, 
        // and extract all others.
        
        $mainDeclIndex = 0;
        foreach ($declarations as $idx => $decl) {
            if ($decl['name'] === $baseName) {
                $mainDeclIndex = $idx;
                break;
            }
        }
        
        $mainDecl = $declarations[$mainDeclIndex];
        
        // Reconstruct main file
        $mainContent = "<?php\n\ndeclare(strict_types=1);\n\n$namespace\n\n$uses\n\n";
        for ($i = $mainDecl['start']; $i <= $mainDecl['end']; $i++) {
            $mainContent .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
        }
        
        file_put_contents($file, $mainContent);
        echo "✅ Refactored main file: $file\n";
        
        // Extract others
        foreach ($declarations as $idx => $decl) {
            if ($idx === $mainDeclIndex) continue;
            
            $newName = $decl['name'];
            $newPath = $dir . '/' . $newName . '.php';
            
            $newContent = "<?php\n\ndeclare(strict_types=1);\n\n$namespace\n\n$uses\n\n";
            for ($i = $decl['start']; $i <= $decl['end']; $i++) {
                $newContent .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
            }
            
            file_put_contents($newPath, $newContent);
            echo "✂️  Extracted: $newPath\n";
        }
    }
}

echo "\n✨ SPLITTING COMPLETE! 1 File = 1 Concept achieved.\n";
