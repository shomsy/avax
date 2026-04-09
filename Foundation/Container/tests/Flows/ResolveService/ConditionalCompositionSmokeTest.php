<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;

interface ConditionalGateway
{
    public function name() : string;
}

final class ActiveConditionalGateway implements ConditionalGateway
{
    public function name() : string
    {
        return 'active';
    }
}

final class OverrideConditionalGateway implements ConditionalGateway
{
    public function name() : string
    {
        return 'override';
    }
}

$activeConfig = CreateContainerConfig::create(settings: [
    'app_env' => 'prod',
    'composition' => [
        'flags' => ['beta'],
        'tenant' => 'tenant-a',
        'region' => 'eu',
        'mode' => 'online',
    ],
]);

$active = makeTestContainer($activeConfig);
$active->singleton(ConditionalGateway::class, ActiveConditionalGateway::class)
    ->asCapability('payments')
    ->asShared()
    ->export()
    ->profiles(['prod'])
    ->flags(['beta'])
    ->tenants(['tenant-a'])
    ->regions(['eu'])
    ->modes(['online'])
    ->because('regional payments gateway');

$activeDescription = $active->describeService(ConditionalGateway::class);
$activeGraph = $active->debugGraph();

assertTrue($active->has(ConditionalGateway::class), 'Active conditional registrations should remain resolvable.');
assertSame('active', $active->get(ConditionalGateway::class)->name(), 'Active conditional registrations should resolve normally.');
assertTrue($activeDescription['conditions']['active'], 'Service descriptions should expose when composition conditions are active.');
assertTrue(
    ($activeGraph['conditions'][ConditionalGateway::class]['active'] ?? false) === true,
    'Graph diagnostics should expose active condition state per service.'
);

$inactiveConfig = CreateContainerConfig::create(settings: [
    'app_env' => 'prod',
    'composition' => [
        'flags' => ['beta'],
        'tenant' => 'tenant-a',
        'region' => 'us',
        'mode' => 'online',
    ],
]);

$inactive = makeTestContainer($inactiveConfig);
$inactive->singleton(ConditionalGateway::class, ActiveConditionalGateway::class)
    ->asCapability('payments')
    ->asShared()
    ->export()
    ->profiles(['prod'])
    ->flags(['beta'])
    ->tenants(['tenant-a'])
    ->regions(['eu'])
    ->modes(['online']);

assertTrue(! $inactive->has(ConditionalGateway::class), 'Inactive conditional registrations should not report as resolvable.');
assertTrue(
    in_array(
        'region [us] is not in [eu]',
        $inactive->describeService(ConditionalGateway::class)['conditions']['reasons'],
        true
    ),
    'Service descriptions should explain why a conditional registration is inactive.'
);

$inactiveIssues = implode("\n", $inactive->validate([ConditionalGateway::class]));
assertTrue(
    str_contains($inactiveIssues, 'region [us] is not in [eu]'),
    'Validation should report the exact inactive composition reason.'
);

try {
    $inactive->get(ConditionalGateway::class);
    throw new RuntimeException('Inactive conditional registrations should fail when resolved.');
} catch (ContainerException $exception) {
    assertTrue(
        str_contains($exception->getMessage(), 'region [us] is not in [eu]'),
        'Runtime failures should explain the inactive composition condition.'
    );
    assertTrue(
        str_contains($exception->getMessage(), 'Likely fix: activate a matching profile, flag set, tenant, region, or mode'),
        'Runtime failures should suggest how to activate a matching composition.'
    );
}

$override = makeTestContainer($activeConfig);
$override->singleton(ConditionalGateway::class, ActiveConditionalGateway::class)
    ->asCapability('payments')
    ->asShared()
    ->export()
    ->profiles(['prod'])
    ->concept('payments.gateway');
$override->singleton(ConditionalGateway::class, OverrideConditionalGateway::class)
    ->asFoundation('foundation.testing')
    ->asInternal()
    ->export(false)
    ->profiles(['prod'])
    ->overrideSource('test-double')
    ->because('test override')
    ->concept('payments.gateway');

$overrideDebug = $override->debugGraph(ConditionalGateway::class);
$overrideIssues = implode("\n", $override->validate([ConditionalGateway::class]));

assertTrue($overrideDebug['overrides'] !== [], 'Graph diagnostics should expose override history for rebound abstracts.');
assertTrue(
    str_contains($overrideIssues, 'Override collision for service [' . ConditionalGateway::class . '] changes ownership posture'),
    'Validation should reject overlapping overrides that change ownership posture silently.'
);

echo basename(__FILE__) . " ok\n";
