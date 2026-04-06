<?php
$base = __DIR__ . '/Foundation/Auth';
$dirs = [
    'Login',
    'Logout',
    'Register',
    'ChangePassword',
    'SessionIdentity',
    'JwtIdentity',
    'PasswordHashing',
    'RequireAuthentication',
    'RequirePermission',
    'RequireRole',
    'Failure',
    'Bridge'
];

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . DIRECTORY_SEPARATOR . $object)) {
                    rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                } else {
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
        }
        rmdir($dir);
        return true;
    }
    return false;
}

foreach ($dirs as $dir) {
    $path = $base . DIRECTORY_SEPARATOR . $dir;
    if (rrmdir($path)) {
        echo "Deleted Legacy: $dir\n";
    }
}
unlink($base . DIRECTORY_SEPARATOR . 'Auth.txt');
unlink($base . DIRECTORY_SEPARATOR . 'merge-files.sh');
unlink(__FILE__);
