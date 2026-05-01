<?php

declare(strict_types=1);

$source = '/home/shomsy/projects/agent-harness/.agents';
$target = '/home/shomsy/projects/avax/.agents/.rules';

function recurse_copy($src, $dst) : void
{
    $dir = opendir($src);
    @mkdir($dst, 0o777, true);
    while ( false !== ($file = readdir($dir)) ) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

echo "Copying $source to $target...\n";
recurse_copy($source, $target);
echo "Done!\n";

// Also copy scaffolds
$skeleton = '/home/shomsy/projects/agent-harness/scaffolds/agents-skeleton';
$visibleAgents = '/home/shomsy/projects/avax/.agents';
if (is_dir($skeleton)) {
    echo "Copying skeleton to $visibleAgents...\n";
    recurse_copy($skeleton, $visibleAgents);
}

// Copy merge-files.sh
copy('/home/shomsy/projects/agent-harness/merge-files.sh', '/home/shomsy/projects/avax/merge-files.sh');
chmod('/home/shomsy/projects/avax/merge-files.sh', 0o755);
