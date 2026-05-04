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

    if (str_contains((string) $path, '/System/PublicSurface/')) {
        $newNs = 'Avax\\Components\\ApplicationWorkflow\\System\\PublicSurface';
        $oldNses = ['namespace components\ApplicationWorkflow;', 'namespace Avax\ApplicationWorkflow;'];
        foreach ($oldNses as $oldNse) {
            if (str_contains($content, $oldNse)) {
                $content = str_replace($oldNse, sprintf('namespace %s;', $newNs), $content);
                $changed = true;
            }
        }
        $useOld = ['use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\\', 'use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\\'];
        $useNew = 'use Avax\\Components\\ApplicationWorkflow\\System\\Capabilities\\Saga\\';
        foreach ($useOld as $oldUse) {
            if (str_contains($content, $oldUse)) {
                $content = str_replace($oldUse, $useNew, $content);
                $changed = true;
            }
        }
    } elseif (str_contains((string) $path, '/System/Capabilities/Saga/')) {
        $newNs = 'Avax\\Components\\ApplicationWorkflow\\System\\Capabilities\\Saga';
        $oldNses = ['namespace components\ApplicationWorkflow\Saga;', 'namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga;'];
        foreach ($oldNses as $oldNse) {
            if (str_contains($content, (string) $oldNse)) {
                $content = str_replace($oldNse, sprintf('namespace %s;', $newNs), $content);
                $changed = true;
            }
        }
        $useOld = ['use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\\', 'use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\\'];
        $useNew = 'use Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\';
        foreach ($useOld as $oldUse) {
            if (str_contains($content, $oldUse)) {
                $content = str_replace($oldUse, $useNew, $content);
                $changed = true;
            }
        }
    } elseif (preg_match('#/System/Flows/Saga/([^/]+)/#', (string) $path, $m)) {
        $group = $m[1];
        $newNs = 'Avax\Components\ApplicationWorkflow\System\Flows\Saga\\' . $group;
        $old1 = sprintf('namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\%s;', $group);
        $old2 = sprintf('namespace components\ApplicationWorkflow\Saga\%s;', $group);
        if (str_contains($content, $old1) || str_contains($content, $old2)) {
            $content = str_replace([$old1, $old2], sprintf('namespace %s;', $newNs), $content);
            $changed = true;
        }
        $useOld = ['use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\\', 'use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\\'];
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
        $rel = substr((string) $path, strpos((string) $path, 'components/'));
        echo sprintf('Updated: %s%s', $rel, PHP_EOL);
    }
}
