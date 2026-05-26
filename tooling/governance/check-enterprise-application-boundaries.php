#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__).'/sdlc/GitChangedFiles.php';

$root = SdlcRuntime::root();
$mode = 'changed';
foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--mode=')) {
        $mode = substr($arg, 7);
    }
}

if (! in_array($mode, ['changed', 'baseline', 'full'], true)) {
    echo "RED: Unsupported mode '{$mode}'. Supported modes: changed, baseline, full.\n";
    exit(1);
}
if ($mode === 'baseline') {
    echo "RED: Baseline mode is not implemented yet for this checker.\n";
    exit(1);
}
if ($mode === 'full') {
    echo "RED: Full mode is not implemented yet for this checker.\n";
    exit(1);
}

SdlcRuntime::printRuntimeHeader('Enterprise Application Boundaries Checker');
$files = GitChangedFiles::all($root);
$signals = ['Repository', 'Gateway', 'Mapper', 'UnitOfWork', 'Transaction', 'Database', 'Persistence', 'Storage', 'Session', 'DTO', 'DataMapper'];
$sensitive = [];
foreach ($files as $file) {
    if (! str_ends_with($file, '.php')) {
        continue;
    }
    if (! preg_match('#^(components|framework|src|app|packages)/#', $file)) {
        continue;
    }
    $content = is_file($root.'/'.$file) ? (string) file_get_contents($root.'/'.$file) : '';
    foreach ($signals as $signal) {
        if (str_contains($file, $signal) || str_contains($content, $signal)) {
            $sensitive[] = $file;
            break;
        }
    }
}

$evidence = array_values(array_filter($files, static fn (string $file): bool => str_starts_with($file, '.agents/management/evidence/generated/') && str_ends_with($file, 'enterprise-application-boundary.md')));
$findings = [];
if ($sensitive !== [] && $evidence === []) {
    $findings[] = 'Enterprise application boundary-sensitive production changes require changed enterprise-application-boundary.md evidence.';
}

$requiredHeadings = ['Task', 'Application Flow', 'Domain Rule', 'Transaction Boundary', 'Persistence Boundary', 'Data Mapping Strategy', 'State Ownership', 'Pattern Chosen', 'Simpler Alternative', 'Why Chosen', 'Consequences', 'Tests / Evidence', 'Review Date'];
foreach ($evidence as $file) {
    $content = (string) file_get_contents($root.'/'.$file);
    foreach ($requiredHeadings as $heading) {
        if (! str_contains($content, '## '.$heading)) {
            $findings[] = "{$file} missing heading: {$heading}";
        }
    }
}

if ($findings === []) {
    echo "GREEN: Enterprise application boundaries check passed. mode={$mode}; sensitive_changes=".count($sensitive)."; evidence_files=".count($evidence)."\n";
    exit(0);
}

echo "RED: Enterprise application boundaries check failed. mode={$mode}\n";
foreach ($findings as $finding) {
    echo "- {$finding}\n";
}
exit(1);
