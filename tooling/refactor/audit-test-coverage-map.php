<?php

$prodFiles = trim(shell_exec('find components framework -name "*.php" | wc -l'));
$testFiles = trim(shell_exec('find tests components -path "*/tests/*.php" -o -path "*/Tests/*.php" | wc -l'));

$markdown = "# Test Coverage Reality Report\n\n";

$markdown .= "## Counts\n";
$markdown .= "- **Production classes/files**: $prodFiles\n";
$markdown .= "- **Test files**: $testFiles\n";
$markdown .= "- **Executed Tests in PHPUnit**: 12 tests, 33 assertions\n\n";

$markdown .= "## Verdict\n";
$markdown .= "The current PHPUnit pass of 12 tests is a false positive for V1 Kernel Green because the coverage is functionally near-zero against an 8875-class codebase. The test suite does not actually test the framework boot cycle or core components.\n\n";

$markdown .= "## Untested Public Surfaces\n";
$markdown .= "- **Framework Public Surface**: Bootstrapping, routing, error handling.\n";
$markdown .= "- **Container**: DI resolution, scopes, compile-time operations.\n";
$markdown .= "- **Cache**: Distributed caching, serialization, TTL jittering.\n";
$markdown .= "- **HTTP**: Request/Response lifecycle, Middleware pipeline, Session management.\n";
$markdown .= "- **Database**: Connections, transactions, query execution.\n\n";

$markdown .= "## Missing Kernel Feature Tests\n";
$markdown .= "- `tests/Feature/Framework/BootApplicationFeatureTest.php`\n";
$markdown .= "- `tests/Feature/Framework/HandleIncomingHttpFeatureTest.php`\n";
$markdown .= "- `tests/Feature/Framework/RunConsoleCommandFeatureTest.php`\n";
$markdown .= "- `tests/Feature/Framework/RequestScopeIsolationFeatureTest.php`\n";
$markdown .= "- `tests/Feature/Framework/WorkerStateResetFeatureTest.php`\n\n";

$markdown .= "## Missing Architecture Tests\n";
$markdown .= "- `tests/Architecture/CrossComponentDependencyTest.php` (Verify Screaming Architecture rules)\n";
$markdown .= "- `tests/Architecture/PublicSurfaceEncapsulationTest.php` (Verify System/Capabilities are not used outside their component)\n";
$markdown .= "- `tests/Architecture/NoSuperglobalsTest.php` (Verify runtime safety)\n\n";

$markdown .= "## Missing Public API Tests\n";
$markdown .= "- `tests/PublicApi/FrameworkPublicApiTest.php`\n";
$markdown .= "- `tests/PublicApi/ContainerPublicApiTest.php`\n";
$markdown .= "- `tests/PublicApi/CachePublicApiTest.php`\n";
$markdown .= "- `tests/PublicApi/HttpPublicApiTest.php`\n";
$markdown .= "- `tests/PublicApi/DatabasePublicApiTest.php`\n\n";

$markdown .= "## Recommendation\n";
$markdown .= "Before V1 Kernel Green can be claimed, the Missing Kernel Feature Tests and Missing Public API Tests MUST be implemented and pass. The test suite MUST grow to cover the major public surfaces.\n";

file_put_contents('EVIDENCE/v1-integrity/test-coverage-reality-report.md', $markdown);
echo "Test Reality Report generated.\n";
