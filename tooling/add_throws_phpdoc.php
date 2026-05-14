<?php
/**
 * Adds @throws tags to methods that throw exceptions but lack @throws PHPDoc.
 *
 * Usage: php tooling/add_throws_phpdoc.php
 */

$dirs = [
    __DIR__ . '/../framework',
    __DIR__ . '/../components',
];

$totalFixed = 0;

foreach ($dirs as $baseDir) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;

        // Find methods with "throw new" but no @throws
        // Pattern: method declaration followed by body containing "throw new"
        if (preg_match_all('/(\/\*\*[\s\S]*?\*\/[\s\n]*)?(public|protected|private)\s+(?:static\s+)?function\s+(\w+)\s*\([^)]*\)\s*:\s*[^{]*\{([^}]*(?:\{[^}]*\}[^}]*)*)\}/', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $fullMatch = $m[0];
                $phpdoc = $m[1] ?? '';
                $visibility = $m[2];
                $methodName = $m[3];
                $body = $m[4];

                // Skip if already has @throws
                if ($phpdoc && preg_match('/\*\s*@throws\s/', $phpdoc)) continue;

                // Find thrown exception classes
                if (preg_match_all('/throw\s+new\s+\\\\?([\w\\\\]+)\s*\(/', $body, $throwMatches, PREG_SET_ORDER)) {
                    $exceptionClasses = array_unique(array_map(fn($t) => ltrim($t[1], '\\'), $throwMatches));

                    if (empty($exceptionClasses)) continue;

                    $throwsTag = '';
                    foreach ($exceptionClasses as $exc) {
                        $shortName = basename(str_replace('\\', '/', $exc));
                        $throwsTag .= " * @throws {$exc}\n";
                    }

                    if ($phpdoc) {
                        // Insert @throws before the closing */
                        $newPhpdoc = preg_replace('/(\s*\*\/)$/', "\n" . $throwsTag . ' */', $phpdoc);
                        $content = str_replace($phpdoc, $newPhpdoc, $content);
                    } else {
                        // Add PHPDoc block before the method
                        $throwsBlock = "/**\n{$throwsTag} */\n";
                        $content = str_replace(
                            "{$visibility} function {$methodName}",
                            "{$throwsBlock}{$visibility} function {$methodName}",
                            $content
                        );
                    }
                    $totalFixed++;
                }
            }
        }

        if ($content !== $original) {
            file_put_contents($path, $content);
        }
    }
}

echo "Added @throws to {$totalFixed} methods\n";
