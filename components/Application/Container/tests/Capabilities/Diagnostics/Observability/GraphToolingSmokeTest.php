<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 3) . '/bootstrap.php';

$tool    = dirname(path: __DIR__, levels: 4) . '/tools/graph.php';
$fixture = dirname(path: __DIR__, levels: 3) . '/fixtures/graph_tool_fixture.php';

$dependencyJson        = shell_exec(command: 'php ' . escapeshellarg(arg: $tool) . ' graph:export ' . escapeshellarg(arg: $fixture) . ' json dependency');
$sliceMermaid          = shell_exec(command: 'php ' . escapeshellarg(arg: $tool) . ' graph:export ' . escapeshellarg(arg: $fixture) . ' mermaid slice');
$policyDot             = shell_exec(command: 'php ' . escapeshellarg(arg: $tool) . ' graph:policy ' . escapeshellarg(arg: $fixture) . ' dot');
$explorerHtml          = shell_exec(command: 'php ' . escapeshellarg(arg: $tool) . ' graph:explore ' . escapeshellarg(arg: $fixture) . ' dependency');
$architectureJson      = shell_exec(command: 'php ' . escapeshellarg(arg: $tool) . ' graph:architecture ' . escapeshellarg(arg: $fixture) . ' json');
$governanceJson        = shell_exec(command: 'php ' . escapeshellarg(arg: $tool) . ' graph:governance ' . escapeshellarg(arg: $fixture));
$diffJson              = shell_exec(command: 'php ' . escapeshellarg(arg: $tool) . ' graph:diff ' . escapeshellarg(arg: $fixture) . ' json');
$sliceJson             = shell_exec(command: 'php ' . escapeshellarg(arg: $tool) . ' graph:slice ' . escapeshellarg(arg: $fixture) . ' flow.login');
$whyJson               = shell_exec(command: 'php ' . escapeshellarg(arg: $tool) . ' why ' . escapeshellarg(arg: $fixture) . ' GraphToolLoginEntry');
$groupJson             = shell_exec(command: 'php ' . escapeshellarg(arg: $tool) . ' group ' . escapeshellarg(arg: $fixture) . ' graph.steps');
$selectionJson         = shell_exec(command: 'php ' . escapeshellarg(arg: $tool) . ' selection ' . escapeshellarg(arg: $fixture) . ' GraphToolIdentityService');
$architectureDebugJson = shell_exec(command: 'php ' . escapeshellarg(arg: $tool) . ' architecture:debug ' . escapeshellarg(arg: $fixture));

assertTrue(condition: is_string(value: $dependencyJson) && str_contains(haystack: $dependencyJson, needle: '"kind": "dependency"'), message: 'Graph tool should export machine-readable dependency graphs.');
assertTrue(condition: is_string(value: $dependencyJson) && str_contains(haystack: $dependencyJson, needle: '"nodes"'), message: 'Dependency graph export should include node payloads.');
assertTrue(condition: is_string(value: $sliceMermaid) && str_contains(haystack: $sliceMermaid, needle: 'flowchart LR'), message: 'Graph tool should export Mermaid graphs.');
assertTrue(condition: is_string(value: $policyDot) && str_contains(haystack: $policyDot, needle: 'digraph container'), message: 'Graph tool should export Graphviz DOT graphs.');
assertTrue(condition: is_string(value: $explorerHtml) && str_contains(haystack: $explorerHtml, needle: 'Container Graph Explorer'), message: 'Graph tool should export an HTML explorer surface.');
assertTrue(condition: is_string(value: $architectureJson) && str_contains(haystack: $architectureJson, needle: '"kind": "architecture"'), message: 'Graph tool should export architecture graph artifacts.');
assertTrue(condition: is_string(value: $governanceJson) && str_contains(haystack: $governanceJson, needle: '"stage": "policy-governance"'), message: 'Graph governance command should expose the governance stage report.');
assertTrue(condition: is_string(value: $diffJson) && str_contains(haystack: $diffJson, needle: '"kind": "diff"'), message: 'Graph diff export should expose a diff artifact.');
assertTrue(
    condition: is_string(value: $diffJson) && (str_contains(haystack: $diffJson, needle: '"ownershipMoves"') || str_contains(haystack: $diffJson, needle: '"addedEdges"')),
    message  : 'Graph diff export should expose structural changes.'
);
assertTrue(condition: is_string(value: $sliceJson) && str_contains(haystack: $sliceJson, needle: '"slice": "flow.login"'), message: 'graph:slice should return per-slice diagnostics.');
assertTrue(condition: is_string(value: $whyJson) && str_contains(haystack: $whyJson, needle: '"why"'), message: 'Architectural debugger commands should return story-grade payloads.');
assertTrue(condition: is_string(value: $groupJson) && str_contains(haystack: $groupJson, needle: '"group": "graph.steps"'), message: 'Group diagnostics command should expose grouped selection state.');
assertTrue(condition: is_string(value: $selectionJson) && str_contains(haystack: $selectionJson, needle: '"service": "GraphToolIdentityService"'), message: 'Selection diagnostics command should expose service selection state.');
assertTrue(condition: is_string(value: $architectureDebugJson) && str_contains(haystack: $architectureDebugJson, needle: '"structuralDrift"'), message: 'Architecture debugger command should expose refactor intelligence output.');

echo basename(path: __FILE__) . " ok\n";
