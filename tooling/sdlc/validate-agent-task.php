#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__.'/SdlcRuntime.php';

$root = SdlcRuntime::root();
SdlcRuntime::requireGit();

function runAgentTaskCommand(string $label, string $command, string $cwd): bool
{
    $result = SdlcRuntime::runCommand($command, $cwd);
    echo '['.($result['exit_code'] === 0 ? 'PASS' : 'FAIL')."] {$label}: exit={$result['exit_code']}\n";
    if ($result['output'] !== '') {
        echo $result['output']."\n";
    }

    return $result['exit_code'] !== 0;
}

SdlcRuntime::printRuntimeHeader('SDLC Agent Task Validation');
$php = escapeshellarg(PHP_BINARY);
$failed = false;
$failed = runAgentTaskCommand('preflight', $php.' tooling/sdlc/preflight.php', $root) || $failed;
$failed = runAgentTaskCommand('validate-changed', $php.' tooling/sdlc/validate-changed.php', $root) || $failed;
$failed = runAgentTaskCommand('validate-governance', $php.' tooling/sdlc/validate-governance.php', $root) || $failed;

if ($failed) {
    echo "RED_BLOCKED: Agent task runner failed one or more required runners.\n";
    exit(1);
}

echo "GREEN_SDLC_AUTOMATION_READY: Agent task runner passed.\n";
exit(0);

