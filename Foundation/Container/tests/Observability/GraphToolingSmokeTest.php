<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$tool = dirname(__DIR__, 2) . '/tools/graph.php';
$fixture = dirname(__DIR__) . '/fixtures/graph_tool_fixture.php';

$dependencyJson = shell_exec('php ' . escapeshellarg($tool) . ' graph:export ' . escapeshellarg($fixture) . ' json dependency');
$sliceMermaid = shell_exec('php ' . escapeshellarg($tool) . ' graph:export ' . escapeshellarg($fixture) . ' mermaid slice');
$policyDot = shell_exec('php ' . escapeshellarg($tool) . ' graph:policy ' . escapeshellarg($fixture) . ' dot');
$diffJson = shell_exec('php ' . escapeshellarg($tool) . ' graph:diff ' . escapeshellarg($fixture) . ' json');
$sliceJson = shell_exec('php ' . escapeshellarg($tool) . ' graph:slice ' . escapeshellarg($fixture) . ' flow.login');
$whyJson = shell_exec('php ' . escapeshellarg($tool) . ' why ' . escapeshellarg($fixture) . ' GraphToolLoginEntry');

assertTrue(is_string($dependencyJson) && str_contains($dependencyJson, '"kind": "dependency"'), 'Graph tool should export machine-readable dependency graphs.');
assertTrue(is_string($dependencyJson) && str_contains($dependencyJson, '"nodes"'), 'Dependency graph export should include node payloads.');
assertTrue(is_string($sliceMermaid) && str_contains($sliceMermaid, 'flowchart LR'), 'Graph tool should export Mermaid graphs.');
assertTrue(is_string($policyDot) && str_contains($policyDot, 'digraph container'), 'Graph tool should export Graphviz DOT graphs.');
assertTrue(is_string($diffJson) && str_contains($diffJson, '"kind": "diff"'), 'Graph diff export should expose a diff artifact.');
assertTrue(
    is_string($diffJson) && (str_contains($diffJson, '"ownershipMoves"') || str_contains($diffJson, '"addedEdges"')),
    'Graph diff export should expose structural changes.'
);
assertTrue(is_string($sliceJson) && str_contains($sliceJson, '"slice": "flow.login"'), 'graph:slice should return per-slice diagnostics.');
assertTrue(is_string($whyJson) && str_contains($whyJson, '"why"'), 'Architectural debugger commands should return story-grade payloads.');

echo basename(__FILE__) . " ok\n";
