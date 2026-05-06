<?php

declare(strict_types=1);

$src = '/home/shomsy/projects/avax/AI Prompts';
$dst = '/home/shomsy/projects/avax/.agents/how-to';

if (! is_dir($dst)) {
    mkdir($dst, 0o777, true);
}

$dir = opendir($src);
while (false !== ($file = readdir($dir))) {
    if (($file !== '.') && ($file !== '..')) {
        rename($src.'/'.$file, $dst.'/'.$file);
    }
}

closedir($dir);
rmdir($src);

echo sprintf('Moved %s to %s%s', $src, $dst, PHP_EOL);
