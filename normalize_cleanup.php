<?php
$base = __DIR__ . '/Foundation/Auth';
$dirs = [
    'Flow/UserLogin',
    'Flow/UserLogout',
    'Flow/UserRegistration',
    'Flow/PasswordChange',
    'Flow/ReadAuthenticationState',
    'Flow/ReadAuthenticatedUser',
    'Security'
];

$files = [
    'Access/EnforceAuthentication.php',
    'Access/EnforcePermission.php',
    'Access/EnforceRole.php',
    'Access/Unauthenticated.php',
    'Access/Unauthorized.php',
    'Support/helpers.php'
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
        echo "Deleted Directory: {$dir}\n";
    }
}

foreach ($files as $file) {
    $path = $base . DIRECTORY_SEPARATOR . $file;
    if (is_file(filename: $path)) {
        unlink(filename: $path);
        echo "Deleted File: {$file}\n";
    }
}
unlink(filename: __FILE__);
