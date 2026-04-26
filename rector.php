<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->paths([
                             __DIR__ . '/Foundation',
                             __DIR__ . '/tests',
                         ]);

    // Define sets of rules
    $rectorConfig->sets([
                            LevelSetList::UP_TO_PHP_84,
                            SetList::CODE_QUALITY,
                            SetList::DEAD_CODE,
                            SetList::EARLY_RETURN,
                            SetList::TYPE_DECLARATION,
                            SetList::STRICT_BOOLEANS,
                            SetList::PRIVATIZATION,
                            SetList::NAMING,
                            SetList::CODING_STYLE,
                        ]);

    // Target PHP 8.5 explicitly
    $rectorConfig->phpVersion(80500);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
