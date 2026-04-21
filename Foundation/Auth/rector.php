<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig) : void {
    $rectorPhpVersion = defined(PhpVersion::class . '::PHP_85')
        ? constant(PhpVersion::class . '::PHP_85')
        : PhpVersion::PHP_84;
    $rectorPhpSet     = defined(SetList::class . '::PHP_85')
        ? constant(SetList::class . '::PHP_85')
        : SetList::PHP_84;

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

    $rectorConfig->phpVersion($rectorPhpVersion);
    $rectorConfig->sets([
                            $rectorPhpSet,
                        ]);
};
