<?php
$dirs = [
    'Login/RateLimit',
    'RequireAuthentication',
    'RequirePermission',
    'RequireRole',
    'Failure',
    'Bridge'
];

foreach ($dirs as $dir) {
    $path = __DIR__ . '/Foundation/Auth/' . $dir;
    if (is_dir(filename: $path)) {
        // Recursive delete
        $it    = new RecursiveDirectoryIterator(directory: $path, flags: FilesystemIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator(iterator: $it, mode: RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir(directory: $file->getRealPath());
            } else {
                unlink(filename: $file->getRealPath());
            }
        }
        rmdir(directory: $path);
        echo "Deleted {$dir}\n";
    }
}
