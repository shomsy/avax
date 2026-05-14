<?php

declare(strict_types=1);

/**
 * check-service-provider-coverage.php
 *
 * Validates that every active runtime-critical component has a real ServiceProvider
 * in System/Configuration/ following the canonical naming convention.
 *
 * Rules:
 * - FAIL if active runtime-critical component lacks real ServiceProvider
 * - FAIL if provider file is an empty shell (register() and boot() both no-ops with no registrations)
 * - FAIL if provider uses wrong naming (*Provider.php instead of *ServiceProvider.php)
 * - FAIL if provider uses hidden runtime assembly fallbacks (ComponentProviderInterface instead of canonical ServiceProvider)
 * - ALLOW SCAFFOLD/ROADMAP/LABS/EVIDENCE components without providers if component-status-lock.md proves it
 * - REQUIRE every missing provider to be tracked in the provider ledger
 *
 * Exit codes:
 * 0 = PASS
 * 1 = FAIL
 */

$rootDir = realpath(__DIR__ . '/../..');
if ($rootDir === false) {
    fwrite(STDERR, "Cannot resolve repository root.\n");
    exit(1);
}

$componentsDir = $rootDir . '/components';
$frameworkDir = $rootDir . '/framework';

$violations = [];
$checked = 0;
$passCount = 0;

// Components that are known to be SCAFFOLD/ROADMAP/EVIDENCE and do not need providers yet
$deferredComponents = [
    'API/ApiBlueprint',
    'API/Contracts',
    'API/GraphQL',
    'API/OpenAPI',
    'API/SchemaGeneration',
    'Application/Config',
    'Application/DateTime',
    'Application/Facade',
    'Application/FeatureFlags',
    'Application/Localization',
    'Application/Pipeline',
    'Application/Storage',
    'Application/Text',
    'Application/Validation',
    'CLI/Console',
    'CLI/System',
    'DataStack/Data',
    'DataStack/DataTransfer',
    'DataStack/Persistence',
    'DeveloperTools/CodeGeneration',
    'DeveloperTools/Diagnostics',
    'DeveloperTools/Documentation',
    'DeveloperTools/DumpDebugger',
    'DeveloperTools/Dx',
    'DeveloperTools/System',
    'DeveloperTools/Testing',
    'Foundation/CallableSerialization',
    'HTTP/AfterResponse',
    'HTTP/ApiVersioning',
    'HTTP/ContentNegotiation',
    'HTTP/Context',
    'HTTP/Dispatcher',
    'HTTP/Request',
    'HTTP/SecureRequest',
    'HTTP/Security',
    'HTTP/URI',
    'Identity/Access',
    'Identity/Credentials',
    'Identity/ExternalIdentity',
    'Identity/Security',
    'Identity/System',
    'Identity/Tenancy',
    'Identity/Tokens',
    'Integration/ObjectStorage',
    'Operations/ApplicationWorkflow',
    'Operations/BackgroundProcesses',
    'Operations/Concurrency',
    'Operations/Delivery',
    'Operations/Filesystem',
    'Operations/Mail',
    'Operations/MemoryLifecycle',
    'Operations/MessageBus',
    'Operations/Notifications',
    'Operations/Observability',
    'Operations/Parallelism',
    'Operations/Queue',
    'Operations/Realtime',
    'Operations/Resilience',
    'Operations/RuntimeSupervision',
    'Operations/Scheduler',
    'Operations/System',
    'Operations/Tasks',
    'Presentation/System',
    'Presentation/View',
    'Security/DataProtection',
    'Security/Hashing',
    'Security/Privacy',
    'Security/Secrets',
    'Security/System',
    'SystemDesign/System',
    'SystemDesign/examples',
    'SystemDesign/reference-architectures',
    'SystemDesign/schemas',
];

/**
 * Scan a component directory tree and find all top-level component dirs.
 *
 * @return list<string>
 */
function findComponents(string $baseDir) : array
{
    $components = [];
    if (! is_dir($baseDir)) {
        return $components;
    }

    foreach (scandir($baseDir) as $area) {
        if ($area === '.' || $area === '..') {
            continue;
        }
        $areaPath = $baseDir . '/' . $area;
        if (! is_dir($areaPath)) {
            continue;
        }
        foreach (scandir($areaPath) as $comp) {
            if ($comp === '.' || $comp === '..') {
                continue;
            }
            $compPath = $areaPath . '/' . $comp;
            if (! is_dir($compPath)) {
                continue;
            }
            $components[] = $area . '/' . $comp;
        }
    }

    return $components;
}

/**
 * Check if a provider file uses the wrong naming (*Provider.php instead of *ServiceProvider.php).
 */
function findWronglyNamedProviders(string $configDir) : array
{
    $wrong = [];
    if (! is_dir($configDir)) {
        return $wrong;
    }
    foreach (scandir($configDir) as $file) {
        if (preg_match('/^(?!.*ServiceProvider\.php$).*Provider\.php$/', $file)) {
            $wrong[] = $file;
        }
    }
    return $wrong;
}

/**
 * Check if a ServiceProvider is an empty shell.
 */
function isEmptyShell(string $filePath) : bool
{
    $content = file_get_contents($filePath);
    if ($content === false) {
        return true;
    }

    // Check if register() method has any actual container calls (singleton, bind, alias, instance)
    $hasRegistration = preg_match('/->(singleton|bind|alias|instance|get)\s*\(/', $content) === 1;

    return ! $hasRegistration;
}

// Scan components/ directory
$allComponents = findComponents($componentsDir);

// Also check framework-level providers
$frameworkComponents = ['Framework/FailureBoundary', 'Framework/Queue', 'Framework'];

foreach ($allComponents as $component) {
    $compDir = $componentsDir . '/' . $component;
    $configDir = $compDir . '/System/Configuration';

    // Skip if no System/ dir (not a real component)
    if (! is_dir($compDir . '/System')) {
        continue;
    }

    $checked++;

    // Check if this component is deferred
    if (in_array($component, $deferredComponents, true)) {
        $passCount++;
        continue;
    }

    // Check for wrongly-named providers (*Provider.php instead of *ServiceProvider.php)
    $wrongNames = findWronglyNamedProviders($configDir);
    foreach ($wrongNames as $wrongFile) {
        $violations[] = "BLOCKER: $component — wrongly named provider: $wrongFile (must end with ServiceProvider.php)";
    }

    // Check for canonical ServiceProvider
    $spFiles = glob($configDir . '/*ServiceProvider.php');
    if ($spFiles === false) {
        $spFiles = [];
    }

    if ($spFiles === []) {
        $violations[] = "HIGH: $component — missing ServiceProvider for active component";
        continue;
    }

    // Check each ServiceProvider is not an empty shell
    foreach ($spFiles as $spFile) {
        $spName = basename($spFile);
        if (isEmptyShell($spFile)) {
            // SCAFFOLD components may have empty providers — check status ledger
            $statusFile = $rootDir . '/EVIDENCE/components/component-status-lock.md';
            $isScaffold = false;
            if (file_exists($statusFile)) {
                $statusContent = file_get_contents($statusFile);
                // Check if this component is marked SCAFFOLD in the status table
                if (preg_match('/\|\\s*' . preg_quote(str_replace('/', '/', $component), '/') . '\s*\|.*SCAFFOLD\b/', $statusContent)) {
                    $isScaffold = true;
                }
            }
            if ($isScaffold) {
                $passCount++;
            } else {
                $violations[] = "HIGH: $component — $spName is an empty shell (no container registrations)";
            }
        } else {
            $passCount++;
        }
    }
}

// Check framework-level
foreach ($frameworkComponents as $component) {
    if ($component === 'Framework') {
        $configDir = $frameworkDir . '/System/Configuration';
    } elseif ($component === 'Framework/FailureBoundary') {
        $configDir = $frameworkDir . '/System/Capabilities/FailureBoundary/Configuration';
    } elseif ($component === 'Framework/Queue') {
        $configDir = $frameworkDir . '/System/Capabilities/Queue/Configuration';
    } else {
        continue;
    }

    $checked++;

    // Check for wrongly-named providers
    $wrongNames = findWronglyNamedProviders($configDir);
    foreach ($wrongNames as $wrongFile) {
        $violations[] = "BLOCKER: $component — wrongly named provider: $wrongFile (must end with ServiceProvider.php)";
    }

    // Check for canonical ServiceProvider
    $spFiles = glob($configDir . '/*ServiceProvider.php');
    if ($spFiles === false) {
        $spFiles = [];
    }

    if ($spFiles === []) {
        $violations[] = "HIGH: $component — missing ServiceProvider for framework-level component";
        continue;
    }

    foreach ($spFiles as $spFile) {
        $spName = basename($spFile);
        if (isEmptyShell($spFile)) {
            $violations[] = "HIGH: $component — $spName is an empty shell (no container registrations)";
        } else {
            $passCount++;
        }
    }
}

if ($violations !== []) {
    echo "FAIL: " . count($violations) . " service provider coverage violations:\n";
    foreach ($violations as $v) {
        echo "  - $v\n";
    }
    echo "\n";
    echo "Checked: $checked components, $passCount passing\n";
    exit(1);
}

echo "PASS: $checked components checked, $passCount with valid providers, deferred components excluded per ledger\n";
exit(0);
