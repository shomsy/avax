<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

$path   = dirname(__DIR__, 3) . '/DI/Flows/CreateContainer/CreateContainer.php';
$source = (string) file_get_contents($path);

assertTrue(str_contains($source, 'AssembleObservability'), 'CreateContainer should only wire observability through its assembly owner.');
assertTrue(str_contains($source, 'AssembleRuntime'), 'CreateContainer should only wire runtime through its assembly owner.');
assertTrue(str_contains($source, 'SeedSystemServices'), 'CreateContainer should seed system services through its dedicated owner.');
assertTrue(! str_contains($source, 'CompileContainer'), 'CreateContainer must not directly wire low-level compile internals.');
assertTrue(! str_contains($source, 'ServiceResolver'), 'CreateContainer must not directly wire the runtime owner.');
assertTrue(! str_contains($source, 'ServicePool'), 'CreateContainer must not directly wire runtime storage internals.');
assertTrue(! str_contains($source, 'HotPathInliner'), 'CreateContainer must not directly wire hot-path internals.');
assertTrue(substr_count($source, PHP_EOL) < 70, 'CreateContainer should stay a small flow owner instead of becoming a hidden god object.');

echo basename(__FILE__) . " ok\n";
