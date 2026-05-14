<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths(paths: [
        __DIR__.'/System',
        __DIR__.'/integrations',
        __DIR__.'/examples',
        __DIR__.'/tooling',
    ]);

    $rectorConfig->skip(skip: [
        __DIR__.'/build',
        __DIR__.'/vendor',
        NullToStrictStringFuncCallArgRector::class => [
            __DIR__.'/System/Capabilities/Identity/UserSource/InMemoryUserSource.php',
            __DIR__.'/integrations/diagnostics/MaskAuditContext.php',
            __DIR__.'/integrations/http/VerifyTrustedProxyHeaders.php',
            __DIR__.'/integrations/release/CheckMigrationPath.php',
            __DIR__.'/integrations/release/CheckSourceTruth.php',
        ],
    ]);

    $rectorConfig->phpVersion(phpVersion: PhpVersion::PHP_85);
    $rectorConfig->sets(sets: [
        SetList::PHP_80,
        SetList::PHP_81,
        SetList::PHP_82,
        SetList::PHP_83,
        SetList::PHP_84,
        SetList::PHP_85,
    ]);
};
