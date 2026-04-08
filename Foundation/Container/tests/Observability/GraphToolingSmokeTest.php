<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$tool = dirname(__DIR__, 2) . '/tools/graph.php';
$fixture = dirname(__DIR__) . '/fixtures/graph_tool_fixture.php';

$dependencyJson = shell_exec('php ' . escapeshellarg($tool) . ' graph:export ' . escapeshellarg($fixture) . ' json dependency');
$sliceMermaid = shell_exec('php ' . escapeshellarg($tool) . ' graph:export ' . escapeshellarg($fixture) . ' mermaid slice');
$policyDot = shell_exec('php ' . escapeshellarg($tool) . ' graph:policy ' . escapeshellarg($fixture) . ' dot');
$explorerHtml = shell_exec('php ' . escapeshellarg($tool) . ' graph:explore ' . escapeshellarg($fixture) . ' dependency');
$architectureJson = shell_exec('php ' . escapeshellarg($tool) . ' graph:architecture ' . escapeshellarg($fixture) . ' json');
$governanceJson = shell_exec('php ' . escapeshellarg($tool) . ' graph:governance ' . escapeshellarg($fixture));
$diffJson = shell_exec('php ' . escapeshellarg($tool) . ' graph:diff ' . escapeshellarg($fixture) . ' json');
$sliceJson = shell_exec('php ' . escapeshellarg($tool) . ' graph:slice ' . escapeshellarg($fixture) . ' flow.login');
$whyJson = shell_exec('php ' . escapeshellarg($tool) . ' why ' . escapeshellarg($fixture) . ' GraphToolLoginEntry');
$groupJson = shell_exec('php ' . escapeshellarg($tool) . ' group ' . escapeshellarg($fixture) . ' graph.steps');
$selectionJson = shell_exec('php ' . escapeshellarg($tool) . ' selection ' . escapeshellarg($fixture) . ' GraphToolIdentityService');
$architectureDebugJson = shell_exec('php ' . escapeshellarg($tool) . ' architecture:debug ' . escapeshellarg($fixture));

assertTrue(is_string($dependencyJson) && str_contains($dependencyJson, '"kind": "dependency"'), 'Graph tool should export machine-readable dependency graphs.');
assertTrue(is_string($dependencyJson) && str_contains($dependencyJson, '"nodes"'), 'Dependency graph export should include node payloads.');
assertTrue(is_string($sliceMermaid) && str_contains($sliceMermaid, 'flowchart LR'), 'Graph tool should export Mermaid graphs.');
assertTrue(is_string($policyDot) && str_contains($policyDot, 'digraph container'), 'Graph tool should export Graphviz DOT graphs.');
assertTrue(is_string($explorerHtml) && str_contains($explorerHtml, 'Container Graph Explorer'), 'Graph tool should export an HTML explorer surface.');
assertTrue(is_string($architectureJson) && str_contains($architectureJson, '"kind": "architecture"'), 'Graph tool should export architecture graph artifacts.');
assertTrue(is_string($governanceJson) && str_contains($governanceJson, '"stage": "policy-governance"'), 'Graph governance command should expose the governance stage report.');
assertTrue(is_string($diffJson) && str_contains($diffJson, '"kind": "diff"'), 'Graph diff export should expose a diff artifact.');
assertTrue(
    is_string($diffJson) && (str_contains($diffJson, '"ownershipMoves"') || str_contains($diffJson, '"addedEdges"')),
    'Graph diff export should expose structural changes.'
);
assertTrue(is_string($sliceJson) && str_contains($sliceJson, '"slice": "flow.login"'), 'graph:slice should return per-slice diagnostics.');
assertTrue(is_string($whyJson) && str_contains($whyJson, '"why"'), 'Architectural debugger commands should return story-grade payloads.');
assertTrue(is_string($groupJson) && str_contains($groupJson, '"group": "graph.steps"'), 'Group diagnostics command should expose grouped selection state.');
assertTrue(is_string($selectionJson) && str_contains($selectionJson, '"service": "GraphToolIdentityService"'), 'Selection diagnostics command should expose service selection state.');
assertTrue(is_string($architectureDebugJson) && str_contains($architectureDebugJson, '"structuralDrift"'), 'Architecture debugger command should expose refactor intelligence output.');

echo basename(__FILE__) . " ok\n";
