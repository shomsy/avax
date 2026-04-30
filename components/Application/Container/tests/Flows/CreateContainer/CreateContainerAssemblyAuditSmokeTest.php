<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, 2) . '/bootstrap.php';

$path = dirname(path: __DIR__, 3) . '/DI/Flows/CreateContainer/CreateContainer.php';
$source = (string) file_get_contents(filename: $path);

assertTrue(condition: str_contains(haystack: $source, needle: 'AssembleObservability'), message: 'CreateContainer should only wire observability through its assembly owner.');
assertTrue(condition: str_contains(haystack: $source, needle: 'AssembleRuntime'), message: 'CreateContainer should only wire runtime through its assembly owner.');
assertTrue(condition: str_contains(haystack: $source, needle: 'SeedSystemServices'), message: 'CreateContainer should seed system services through its dedicated owner.');
assertTrue(condition: ! str_contains(haystack: $source, needle: 'CompileContainer'), message: 'CreateContainer must not directly wire low-level compile internals.');
assertTrue(condition: ! str_contains(haystack: $source, needle: 'ServiceResolver'), message: 'CreateContainer must not directly wire the runtime owner.');
assertTrue(condition: ! str_contains(haystack: $source, needle: 'ServicePool'), message: 'CreateContainer must not directly wire runtime storage internals.');
assertTrue(condition: ! str_contains(haystack: $source, needle: 'HotPathInliner'), message: 'CreateContainer must not directly wire hot-path internals.');
assertTrue(condition: substr_count(haystack: $source, needle: PHP_EOL) < 70, message: 'CreateContainer should stay a small flow owner instead of becoming a hidden god object.');

echo basename(path: __FILE__) . " ok\n";
