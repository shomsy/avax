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

function rrmdir($dir)
{
    if (is_dir(filename: $dir)) {
        $objects = scandir(directory: $dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir(filename: $dir . DIRECTORY_SEPARATOR . $object) && ! is_link(filename: $dir . DIRECTORY_SEPARATOR . $object)) {
                    rrmdir(dir: $dir . DIRECTORY_SEPARATOR . $object);
                } else {
                    unlink(filename: $dir . DIRECTORY_SEPARATOR . $object);
                }
            }
        }
        rmdir(directory: $dir);

        return true;
    }

    return false;
}

foreach ($dirs as $dir) {
    $path = $base . DIRECTORY_SEPARATOR . $dir;
    if (rrmdir(dir: $path)) {
        echo "Deleted Legacy: {$dir}\n";
    }
}
unlink(filename: $base . DIRECTORY_SEPARATOR . 'Auth.txt');
unlink(filename: $base . DIRECTORY_SEPARATOR . 'merge-files.sh');
unlink(filename: __FILE__);
