<?php

declare(strict_types=1);

$directories = ['components', 'framework'];
$violations  = [];

foreach ($directories as $dir) {
    if (! is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $path   = $file->getPathname();
        $code   = file_get_contents($path);
        $tokens = token_get_all($code);

        $declarations = [];
        $count        = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if (is_array($token)) {
                $type  = $token[0];
                $value = $token[1];

                if (in_array($type, [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM])) {

                    // Skip ::class
                    $isDoubleColon = false;
                    for ($j = $i - 1; $j >= 0; $j--) {
                        if (! is_array($tokens[$j]) && $tokens[$j] === ':') {
                            continue;
                        } // Not ::
                        if (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                            continue;
                        }
                        if (is_array($tokens[$j]) && $tokens[$j][0] === T_DOUBLE_COLON) {
                            $isDoubleColon = true;
                        }

                        break;
                    }
                    if ($isDoubleColon) {
                        continue;
                    }

                    // Skip anonymous classes (new class)
                    $isAnonymous = false;
                    for ($j = $i - 1; $j >= 0; $j--) {
                        if (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                            continue;
                        }
                        if (is_array($tokens[$j]) && $tokens[$j][0] === T_NEW) {
                            $isAnonymous = true;
                        }

                        break;
                    }
                    if ($isAnonymous) {
                        continue;
                    }

                    // Find the name of the class/interface/trait/enum
                    $name = 'Unknown';
                    for ($j = $i + 1; $j < $count; $j++) {
                        if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                            $name = $tokens[$j][1];

                            break;
                        }
                    }

                    $declarations[] = token_name($type) . ' find_multiple_classes.php' . $name;
                }
            }
        }

        if (count($declarations) > 1) {
            $violations[$path] = $declarations;
        }
    }
}

echo "=== MULTI-CLASS FILE AUDIT ===\n\n";

if (empty($violations)) {
    echo "✨ PERFECT! Every file contains exactly one class/interface/trait/enum.\n";
} else {
    foreach ($violations as $file => $declarations) {
        echo "🚨 VIOLATION IN: $file\n";
        echo '   Found ' . count($declarations) . " declarations:\n";
        foreach ($declarations as $dec) {
            echo "    - $dec\n";
        }
        echo "\n";
    }
    echo "Total files violating 'One File = One Concept': " . count($violations) . "\n";
}
