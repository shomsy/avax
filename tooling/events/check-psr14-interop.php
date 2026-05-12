#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * V5.7 — PSR-14 Interop Gate
 *
 * Verifies that:
 * 1. psr/event-dispatcher is installed if PSR-14 is GREEN
 * 2. PSR adapter class exists
 * 3. PSR listener provider adapter exists
 * 4. adapter delegates to AvaX canonical runtime
 * 5. adapter does not bypass registry
 */

$root = dirname(__DIR__, 2);

$exitCode = 0;
$checks = [];

// --- Check 1: psr/event-dispatcher is installed ---
$composerJsonPath = $root . '/composer.json';
$checks['composer_json_exists'] = file_exists($composerJsonPath);

$psrInstalled = false;
if (file_exists($composerJsonPath)) {
    $composerJson = file_get_contents($composerJsonPath);
    $psrInstalled = str_contains($composerJson, 'psr/event-dispatcher');
}
$checks['psr_event_dispatcher_installed'] = $psrInstalled;

// Also check if the PSR interface classes exist via class_exists or file check
$psrInterfaceAvailable = false;
$vendorPsrPath = $root . '/vendor/psr/event-dispatcher/src/EventDispatcherInterface.php';
if (file_exists($vendorPsrPath)) {
    $psrInterfaceAvailable = true;
}
$checks['psr_event_dispatcher_vendor_available'] = $psrInterfaceAvailable;

// --- Check 2: PSR EventDispatcherAdapter exists ---
$psrDispatcherAdapterPath = $root . '/components/Operations/Events/System/Capabilities/Psr14/Psr14EventDispatcherAdapter.php';
$checks['Psr14EventDispatcherAdapter_file_exists'] = file_exists($psrDispatcherAdapterPath);

if (file_exists($psrDispatcherAdapterPath)) {
    $adapterContent = file_get_contents($psrDispatcherAdapterPath);

    // --- Check 3: Implements PSR-14 EventDispatcherInterface ---
    $checks['Psr14EventDispatcherAdapter_implements_PSR14'] = str_contains($adapterContent, 'EventDispatcherInterface');

    // --- Check 4: Delegates to AvaX EventEmitter ---
    $checks['Psr14EventDispatcherAdapter_delegates_to_avax'] = str_contains($adapterContent, 'EventEmitter')
        && str_contains($adapterContent, '->emit(');

    // --- Check 5: Does not bypass AvaX registry ---
    // The adapter should NOT directly access CompiledListenerRegistry or raw listeners
    $checks['Psr14EventDispatcherAdapter_does_not_bypass_registry'] = ! str_contains($adapterContent, 'CompiledListenerRegistry')
        && ! str_contains($adapterContent, 'getListenersFor')
        && str_contains($adapterContent, 'avaxEmitter');

    // Has dispatch method with correct signature
    $checks['Psr14EventDispatcherAdapter_has_dispatch'] = str_contains($adapterContent, 'public function dispatch(object $event): object');
} else {
    $checks['Psr14EventDispatcherAdapter_implements_PSR14'] = false;
    $checks['Psr14EventDispatcherAdapter_delegates_to_avax'] = false;
    $checks['Psr14EventDispatcherAdapter_does_not_bypass_registry'] = false;
    $checks['Psr14EventDispatcherAdapter_has_dispatch'] = false;
}

// --- Check 6: PSR ListenerProviderAdapter exists ---
$psrProviderAdapterPath = $root . '/components/Operations/Events/System/Capabilities/Psr14/Psr14ListenerProviderAdapter.php';
$checks['Psr14ListenerProviderAdapter_file_exists'] = file_exists($psrProviderAdapterPath);

if (file_exists($psrProviderAdapterPath)) {
    $providerContent = file_get_contents($psrProviderAdapterPath);

    // --- Check 7: Implements PSR-14 ListenerProviderInterface ---
    $checks['Psr14ListenerProviderAdapter_implements_PSR14'] = str_contains($providerContent, 'ListenerProviderInterface');

    // --- Check 8: Delegates to AvaX CompiledListenerRegistry ---
    $checks['Psr14ListenerProviderAdapter_delegates_to_avax'] = str_contains($providerContent, 'CompiledListenerRegistry')
        && str_contains($providerContent, 'getListenersFor');

    // --- Check 9: Does not bypass registry with raw reflection ---
    $checks['Psr14ListenerProviderAdapter_does_not_bypass_registry'] = ! str_contains($providerContent, 'getAttributes')
        || ! preg_match('/getListenersForEvent.*getAttributes/s', $providerContent);

    // Has getListenersForEvent method
    $checks['Psr14ListenerProviderAdapter_has_method'] = str_contains($providerContent, 'public function getListenersForEvent');
} else {
    $checks['Psr14ListenerProviderAdapter_implements_PSR14'] = false;
    $checks['Psr14ListenerProviderAdapter_delegates_to_avax'] = false;
    $checks['Psr14ListenerProviderAdapter_does_not_bypass_registry'] = false;
    $checks['Psr14ListenerProviderAdapter_has_method'] = false;
}

// --- Report ---
echo "EVENT PSR-14 INTEROP GATE\n";
echo str_repeat('=', 60) . "\n\n";

$allPass = true;
foreach ($checks as $name => $pass) {
    $status = $pass ? 'PASS' : 'FAIL';
    if (! $pass) {
        $allPass = false;
        $exitCode = 1;
    }
    printf("  [%s] %s\n", $status, $name);
}

echo "\n";
echo str_repeat('=', 60) . "\n";

if ($allPass) {
    echo "Result: PASS — PSR-14 interop verified.\n";
} else {
    echo "Result: FAIL — PSR-14 interop gate failed.\n";
}

exit($exitCode);
