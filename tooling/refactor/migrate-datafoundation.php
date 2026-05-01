#!/usr/bin/env php
<?php

declare(strict_types=1);
$root     = __DIR__ . '/../..';
$dfRoot   = $root . '/components/DataFoundation';
$dataRoot = $root . '/components/Data/System';

$migrations = [
    'Flows/Batch/Batch.php'             => 'Flows/Batch/Batch.php',
    'Flows/Pipeline/Pipeline.php'       => 'Flows/Pipeline/Pipeline.php',
    'Flows/Pipeline/Pipe.php'           => 'Flows/Pipeline/Pipe.php',
    'Flows/LazySequence/LazySequence.php' => 'Flows/LazySequence/LazySequence.php',
    'Flows/Window/Window.php'           => 'Flows/Window/Window.php',
    'Values/Option/Option.php'          => 'Capabilities/Collections/Internal/Values/Option/Option.php',
    'Values/Option/Some.php'            => 'Capabilities/Collections/Internal/Values/Option/Some.php',
    'Values/Option/None.php'            => 'Capabilities/Collections/Internal/Values/Option/None.php',
    'Values/Result/Result.php'          => 'Capabilities/Collections/Internal/Values/Result/Result.php',
    'Values/Result/Ok.php'              => 'Capabilities/Collections/Internal/Values/Result/Success.php',
    'Values/Result/Error.php'           => 'Capabilities/Collections/Internal/Values/Result/Failure.php',
    'Composites/MapEntry/MapEntry.php'  => 'Capabilities/Collections/Internal/Composites/MapEntry.php',
    'Composites/Pair/Pair.php'          => 'Capabilities/Collections/Internal/Composites/Pair.php',
    'Composites/Tuple/Tuple2.php'       => 'Capabilities/Collections/Internal/Composites/Tuple2.php',
    'Composites/Tuple/Tuple3.php'       => 'Capabilities/Collections/Internal/Composites/Tuple3.php',
    'Composites/Tuple/Tuple4.php'       => 'Capabilities/Collections/Internal/Composites/Tuple4.php',
    'Composites/Record/Record.php'      => 'Capabilities/Collections/Internal/Composites/Record.php',
    'Composites/Record/RecordField.php' => 'Capabilities/Collections/Internal/Composites/RecordField.php',
];

$copied = 0;
foreach ($migrations as $srcRel => $dstRel) {
    $src = "$dfRoot/$srcRel";
    $dst = "$dataRoot/$dstRel";
    if (! file_exists($src)) {
        echo "MISSING $src\n";

        continue;
    }
    $dstDir = dirname($dst);
    if (! is_dir($dstDir)) {
        mkdir($dstDir, 0o777, true);
    }
    copy($src, $dst);
    $content = file_get_contents($dst);
    $dir    = dirname($dstRel);
    $newNs  = 'Avax\\Components\\Data\\System\\' . str_replace('/', '\\', $dir);
    $content = preg_replace('#^namespace\s+.*;$#m', "namespace {$newNs};", $content, 1, $count);
    if ($count === 0) {
        $content = "namespace {$newNs};\n\n" . $content;
    }
    $content = str_replace(['use Avax\DataFoundation\\', 'use components\DataFoundation\\'], 'use Avax\\Components\\Data\\', $content);
    file_put_contents($dst, $content);
    echo "Migrated: $srcRel -> $dstRel\n";
    $copied++;
}

// Validation rules directory copy
$valSrc         = "$dfRoot/Validation/Attributes/Rules";
$valDstBase = "$dataRoot/Capabilities/Validation/Rules";
if (is_dir($valSrc)) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($valSrc, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $absPath = $file->getRealPath();
        $rel    = substr($absPath, strlen($valSrc) + 1);
        $dst    = "$valDstBase/$rel";
        $dstDir = dirname($dst);
        if (! is_dir($dstDir)) {
            mkdir($dstDir, 0o777, true);
        }
        copy($absPath, $dst);
        $content = file_get_contents($dst);
        $relNoExt = str_replace('.php', '', $rel);
        $dir     = dirname($relNoExt);
        $newNs   = 'Avax\\Components\\Data\\System\\Capabilities\\Validation\\Rules';
        if ($dir !== '.' && $dir !== '') {
            $newNs .= '\\' . str_replace('/', '\\', $dir);
        }
        $content = preg_replace('#^namespace\s+.*;$#m', "namespace {$newNs};", $content, 1, $c);
        if ($c === 0) {
            $content = "namespace {$newNs};\n\n" . $content;
        }
        $content = str_replace(['use Avax\DataFoundation\\', 'use components\DataFoundation\\'], 'use Avax\\Components\\Data\\', $content);
        file_put_contents($dst, $content);
        echo "Migrated Validation/Rules: $rel\n";
        $copied++;
    }
}

echo "\nTotal migrated: $copied\n";
