<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->paths([
                             __DIR__ . '/System',
                             __DIR__ . '/integrations',
                             __DIR__ . '/examples',
                             __DIR__ . '/tooling',
                         ]);

    $rectorConfig->skip([
                            __DIR__ . '/build',
                            __DIR__ . '/vendor',
                        ]);

    $rectorConfig->phpVersion(PhpVersion::PHP_85);
    $rectorConfig->sets([
                            SetList::PHP_85,
                        ]);
};
