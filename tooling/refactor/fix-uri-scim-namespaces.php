<?php

$dirs = ['components', 'tests', 'framework'];
$files = [];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $out = trim(shell_exec("find $dir -type f -name \"*.php\""));
        if ($out) {
            $files = array_merge($files, explode("\n", $out));
        }
    }
}

$count = 0;
foreach ($files as $file) {
    if (! $file) {
        continue;
    }
    $content = file_get_contents($file);
    $changed = false;

    // 1. URI Parts Fix
    // Old namespaces (Avax and components prefix)
    $oldUriNamespaces = [
        'Avax\Components\HTTP\URI\Parts',
        'components\HTTP\URI\Parts',
    ];
    $newUriNamespace = 'Avax\Components\HTTP\URI\System\Capabilities\Parts';

    foreach ($oldUriNamespaces as $old) {
        if (str_contains($content, $old)) {
            $content = str_replace($old, $newUriNamespace, $content);
            $changed = true;
        }
    }

    // 2. SCIM Support -> Directories Fix
    $oldScimSupport = 'Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support';
    $newScimDirectories = 'Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories';

    if (str_contains($content, $oldScimSupport)) {
        $content = str_replace($oldScimSupport, $newScimDirectories, $content);
        $changed = true;
    }

    if ($changed) {
        file_put_contents($file, $content);
        echo "Fixed namespaces in $file\n";
        $count++;
    }
}
echo "Fixed $count files.\n";
