<?php

declare(strict_types=1);

$paths = ['framework', 'components'];
$multiClassFiles = [];

foreach ($paths as $path) {
    $dir = __DIR__.'/../../'.$path;
    if (! is_dir($dir)) {
        continue;
    }

    $directory = new RecursiveDirectoryIterator($dir);
    $iterator = new RecursiveIteratorIterator($directory);
    $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

    foreach ($regex as $file) {
        $content = file_get_contents($file[0]);
        $tokens = token_get_all($content);

        $declarations = 0;
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (is_array($token) && in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM], true)) {
                // Check if it's ::class or some other constant usage
                $isClassConstant = false;

                // Find previous non-whitespace/comment token
                $prev = $i - 1;
                while ($prev >= 0) {
                    $pt = $tokens[$prev];
                    if (is_array($pt) && in_array($pt[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                        $prev--;

                        continue;
                    }
                    break;
                }

                if ($prev >= 0) {
                    $pt = $tokens[$prev];
                    if (is_array($pt) && $pt[0] === T_DOUBLE_COLON) {
                        $isClassConstant = true;
                    }
                }

                if (! $isClassConstant) {
                    // Also check for anonymous classes: "new class"
                    $isAnonymous = false;
                    $prev = $i - 1;
                    while ($prev >= 0) {
                        $pt = $tokens[$prev];
                        if (is_array($pt) && in_array($pt[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                            $prev--;

                            continue;
                        }
                        break;
                    }
                    if ($prev >= 0) {
                        $pt = $tokens[$prev];
                        if (is_array($pt) && $pt[0] === T_NEW) {
                            $isAnonymous = true;
                        }
                    }

                    if (! $isAnonymous) {
                        $declarations++;
                    }
                }
            }
        }

        if ($declarations > 1) {
            $multiClassFiles[] = [
                'file' => str_replace(__DIR__.'/../../', '', $file[0]),
                'declarations' => $declarations,
            ];
        }
    }
}

echo "# Multi-Class File Audit Report\n\n";
if (empty($multiClassFiles)) {
    echo "No multi-class files found in production paths.\n";
} else {
    echo "| File | Declarations |\n";
    echo "|---|---|\n";
    foreach ($multiClassFiles as $item) {
        echo "| {$item['file']} | {$item['declarations']} |\n";
    }
}
