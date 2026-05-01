#!/usr/bin/env php
<?php

declare(strict_types=1);
$base = __DIR__ . '/../../components/ApplicationWorkflow/System';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $path     = $file->getPathname();
    $content = file_get_contents($path);
    $changed = false;

    if (str_contains($path, '/System/PublicSurface/')) {
        $newNs = 'Avax\\Components\\ApplicationWorkflow\\System\\PublicSurface';
        $oldNses = ['namespace components\ApplicationWorkflow;', 'namespace Avax\ApplicationWorkflow;'];
        foreach ($oldNses as $old) {
            if (str_contains($content, $old)) {
                $content = str_replace($old, "namespace {$newNs};", $content);
                $changed = true;
            }
        }
        $useOld = ['use components\ApplicationWorkflow\Saga\\', 'use Avax\ApplicationWorkflow\Saga\\'];
        $useNew = 'use Avax\\Components\\ApplicationWorkflow\\System\\Capabilities\\Saga\\';
        foreach ($useOld as $oldUse) {
            if (str_contains($content, $oldUse)) {
                $content = str_replace($oldUse, $useNew, $content);
                $changed = true;
            }
        }
    } elseif (str_contains($path, '/System/Capabilities/Saga/')) {
        $newNs = 'Avax\\Components\\ApplicationWorkflow\\System\\Capabilities\\Saga';
        $oldNses = ['namespace components\ApplicationWorkflow\Saga;', 'namespace Avax\ApplicationWorkflow\Saga;'];
        foreach ($oldNses as $old) {
            if (str_contains($content, $old)) {
                $content = str_replace($old, "namespace {$newNs};", $content);
                $changed = true;
            }
        }
        $useOld = ['use components\ApplicationWorkflow\Saga\\', 'use Avax\ApplicationWorkflow\Saga\\'];
        $useNew = 'use Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\';
        foreach ($useOld as $oldUse) {
            if (str_contains($content, $oldUse)) {
                $content = str_replace($oldUse, $useNew, $content);
                $changed = true;
            }
        }
    } elseif (preg_match('#/System/Flows/Saga/([^/]+)/#', $path, $m)) {
        $group = $m[1];
        $newNs = "Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\{$group}";
        $old1 = "namespace Avax\ApplicationWorkflow\Saga\\{$group};";
        $old2 = "namespace components\ApplicationWorkflow\Saga\\{$group};";
        if (str_contains($content, $old1) || str_contains($content, $old2)) {
            $content = str_replace([$old1, $old2], "namespace {$newNs};", $content);
            $changed = true;
        }
        $useOld = ['use Avax\ApplicationWorkflow\Saga\\', 'use components\ApplicationWorkflow\Saga\\'];
        $useNew = 'use Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\';
        foreach ($useOld as $oldUse) {
            if (str_contains($content, $oldUse)) {
                $content = str_replace($oldUse, $useNew, $content);
                $changed = true;
            }
        }
    }

    if ($changed) {
        file_put_contents($path, $content);
        $rel = substr($path, strpos($path, 'components/'));
        echo "Updated: $rel\n";
    }
}
