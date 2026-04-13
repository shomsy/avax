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
                                                            'app_env'     => 'prod',
                                                            'composition' => [
                                                                'flags'  => ['beta'],
                                                                'tenant' => 'tenant-a',
                                                                'region' => 'eu',
                                                                'mode'   => 'online',
                                                            ],
                                                        ]);

$active = makeTestContainer(config: $activeConfig);
$active->singleton(abstract: ConditionalGateway::class, concrete: ActiveConditionalGateway::class)
    ->asCapability(ownerSlice: 'payments')
    ->asShared()
    ->export()
    ->profiles(profiles: ['prod'])
    ->flags(flags: ['beta'])
    ->tenants(tenants: ['tenant-a'])
    ->regions(regions: ['eu'])
    ->modes(modes: ['online'])
    ->because(reason: 'regional payments gateway');

$activeDescription = $active->describeService(id: ConditionalGateway::class);
$activeGraph       = $active->debugGraph();

assertTrue(condition: $active->has(id: ConditionalGateway::class), message: 'Active conditional registrations should remain resolvable.');
assertSame(expected: 'active', actual: $active->get(id: ConditionalGateway::class)->name(), message: 'Active conditional registrations should resolve normally.');
assertTrue(condition: $activeDescription['conditions']['active'], message: 'Service descriptions should expose when composition conditions are active.');
assertTrue(
    condition: ($activeGraph['conditions'][ConditionalGateway::class]['active'] ?? false) === true,
    message  : 'Graph diagnostics should expose active condition state per service.'
);

$inactiveConfig = CreateContainerConfig::create(settings: [
                                                              'app_env'     => 'prod',
                                                              'composition' => [
                                                                  'flags'  => ['beta'],
                                                                  'tenant' => 'tenant-a',
                                                                  'region' => 'us',
                                                                  'mode'   => 'online',
                                                              ],
                                                          ]);

$inactive = makeTestContainer(config: $inactiveConfig);
$inactive->singleton(abstract: ConditionalGateway::class, concrete: ActiveConditionalGateway::class)
    ->asCapability(ownerSlice: 'payments')
    ->asShared()
    ->export()
    ->profiles(profiles: ['prod'])
    ->flags(flags: ['beta'])
    ->tenants(tenants: ['tenant-a'])
    ->regions(regions: ['eu'])
    ->modes(modes: ['online']);

assertTrue(condition: ! $inactive->has(id: ConditionalGateway::class), message: 'Inactive conditional registrations should not report as resolvable.');
assertTrue(
    condition: in_array(
        'region [us] is not in [eu]',
        $inactive->describeService(id: ConditionalGateway::class)['conditions']['reasons'],
        true
    ),
    message  : 'Service descriptions should explain why a conditional registration is inactive.'
);

$inactiveIssues = implode("\n", $inactive->validate(serviceIds: [ConditionalGateway::class]));
assertTrue(
    condition: str_contains($inactiveIssues, 'region [us] is not in [eu]'),
    message  : 'Validation should report the exact inactive composition reason.'
);

try {
    $inactive->get(id: ConditionalGateway::class);
    throw new RuntimeException(message: 'Inactive conditional registrations should fail when resolved.');
} catch (ContainerException $exception) {
    assertTrue(
        condition: str_contains($exception->getMessage(), 'region [us] is not in [eu]'),
        message  : 'Runtime failures should explain the inactive composition condition.'
    );
    assertTrue(
        condition: str_contains($exception->getMessage(), 'Likely fix: activate a matching profile, flag set, tenant, region, or mode'),
        message  : 'Runtime failures should suggest how to activate a matching composition.'
    );
}

$override = makeTestContainer(config: $activeConfig);
$override->singleton(abstract: ConditionalGateway::class, concrete: ActiveConditionalGateway::class)
    ->asCapability(ownerSlice: 'payments')
    ->asShared()
    ->export()
    ->profiles(profiles: ['prod'])
    ->concept(concept: 'payments.gateway');
$override->singleton(abstract: ConditionalGateway::class, concrete: OverrideConditionalGateway::class)
    ->asFoundation(ownerSlice: 'foundation.testing')
    ->asInternal()
    ->export(exported: false)
    ->profiles(profiles: ['prod'])
    ->overrideSource(source: 'test-double')
    ->because(reason: 'test override')
    ->concept(concept: 'payments.gateway');

$overrideDebug  = $override->debugGraph(id: ConditionalGateway::class);
$overrideIssues = implode("\n", $override->validate(serviceIds: [ConditionalGateway::class]));

assertTrue(condition: $overrideDebug['overrides'] !== [], message: 'Graph diagnostics should expose override history for rebound abstracts.');
assertTrue(
    condition: str_contains($overrideIssues, 'Override collision for service [' . ConditionalGateway::class . '] changes ownership posture'),
    message  : 'Validation should reject overlapping overrides that change ownership posture silently.'
);

echo basename(__FILE__) . " ok\n";
