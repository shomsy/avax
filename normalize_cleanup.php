<?php
$base = __DIR__ . '/Foundation/Auth';
$dirs = [
    'Flows/UserLogin',
    'Flows/UserLogout',
    'Flows/UserRegistration',
    'Flows/PasswordChange',
    'Flows/ReadAuthenticationState',
    'Flows/ReadAuthenticatedUser',
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
        echo "Deleted Directory: $dir\n";
    }
}

foreach ($files as $file) {
    $path = $base . DIRECTORY_SEPARATOR . $file;
    if (is_file($path)) {
        unlink($path);
        echo "Deleted File: $file\n";
    }
}
unlink(__FILE__);
