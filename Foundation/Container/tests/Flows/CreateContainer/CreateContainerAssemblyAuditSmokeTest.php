<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

$path   = dirname(__DIR__, 3) . '/DI/Flows/CreateContainer/CreateContainer.php';
$source = (string) file_get_contents($path);

assertTrue(condition: str_contains($source, 'AssembleObservability'), message: 'CreateContainer should only wire observability through its assembly owner.');
assertTrue(condition: str_contains($source, 'AssembleRuntime'), message: 'CreateContainer should only wire runtime through its assembly owner.');
assertTrue(condition: str_contains($source, 'SeedSystemServices'), message: 'CreateContainer should seed system services through its dedicated owner.');
assertTrue(condition: ! str_contains($source, 'CompileContainer'), message: 'CreateContainer must not directly wire low-level compile internals.');
assertTrue(condition: ! str_contains($source, 'ServiceResolver'), message: 'CreateContainer must not directly wire the runtime owner.');
assertTrue(condition: ! str_contains($source, 'ServicePool'), message: 'CreateContainer must not directly wire runtime storage internals.');
assertTrue(condition: ! str_contains($source, 'HotPathInliner'), message: 'CreateContainer must not directly wire hot-path internals.');
assertTrue(condition: substr_count($source, PHP_EOL) < 70, message: 'CreateContainer should stay a small flow owner instead of becoming a hidden god object.');

echo basename(__FILE__) . " ok\n";
