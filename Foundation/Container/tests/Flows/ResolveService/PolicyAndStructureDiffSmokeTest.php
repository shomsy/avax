<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\ContainerInterface;
use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class PolicyDependencyA
{
}

final class PolicyDependencyB
{
}

final class PolicyDependencyC
{
}

final class PolicyDependencyD
{
}

final class PolicyDependencyE
{
}

final class PolicyDependencyF
{
}

final class OverInjectedPolicyService
{
    public function __construct(
        PolicyDependencyA $a,
        PolicyDependencyB $b,
        PolicyDependencyC $c,
        PolicyDependencyD $d,
        PolicyDependencyE $e,
        PolicyDependencyF $f
    ) {
    }
}

final class OtherFlowLocal
{
}

final class FlowToFlowEntry
{
    public function __construct(public OtherFlowLocal $local)
    {
    }
}

final class GenericHelperService
{
}

final class StructureDiffService
{
}

final class LocatorDriftService
{
    public function __construct(public ContainerInterface $container)
    {
    }
}

$cacheDir = sys_get_temp_dir() . '/container-policy-diff-' . uniqid('', true);
$config = CreateContainerConfig::create(cacheDir: $cacheDir);
$container = makeTestContainer($config);

$container->bind(PolicyDependencyA::class, PolicyDependencyA::class);
$container->bind(PolicyDependencyB::class, PolicyDependencyB::class);
$container->bind(PolicyDependencyC::class, PolicyDependencyC::class);
$container->bind(PolicyDependencyD::class, PolicyDependencyD::class);
$container->bind(PolicyDependencyE::class, PolicyDependencyE::class);
$container->bind(PolicyDependencyF::class, PolicyDependencyF::class);
$container->singleton(OverInjectedPolicyService::class, OverInjectedPolicyService::class)
    ->asCapability('checkout.policy')
    ->asShared()
    ->export();
$container->bind(OtherFlowLocal::class, OtherFlowLocal::class)
    ->asFlow('read-user')
    ->asPrivate();
$container->bind(FlowToFlowEntry::class, FlowToFlowEntry::class)
    ->asFlow('login')
    ->asPrivate()
    ->entry();
$container->singleton(GenericHelperService::class, GenericHelperService::class)
    ->asCapability('misc.naming')
    ->asShared()
    ->export()
    ->concept('helper');
$container->singleton(StructureDiffService::class, StructureDiffService::class)
    ->asCapability('billing')
    ->asShared()
    ->export();
$container->bind(LocatorDriftService::class, LocatorDriftService::class)
    ->asCapability('billing')
    ->asShared()
    ->export();

$container->compileContainer([StructureDiffService::class]);
$container->singleton(StructureDiffService::class, StructureDiffService::class)
    ->asFoundation('foundation.diff')
    ->asPublic()
    ->concept('structure.diff');

$graph = $container->debugGraph();
$serviceGraph = $container->debugGraph(StructureDiffService::class);
$issues = implode("\n", $container->validate([
    OverInjectedPolicyService::class,
    FlowToFlowEntry::class,
    GenericHelperService::class,
    StructureDiffService::class,
    LocatorDriftService::class,
]));

$overInjectedCodes = array_column($graph['policyFindings'][OverInjectedPolicyService::class] ?? [], 'code');
$flowCodes = array_column($graph['policyFindings'][FlowToFlowEntry::class] ?? [], 'code');
$genericCodes = array_column($graph['policyFindings'][GenericHelperService::class] ?? [], 'code');
$locatorCodes = array_column($graph['policyFindings'][LocatorDriftService::class] ?? [], 'code');

assertTrue(in_array('POL-001', $overInjectedCodes, true), 'Policy diagnostics should flag over-injected constructors.');
assertTrue(in_array('POL-004', $flowCodes, true), 'Policy diagnostics should flag direct flow-to-flow dependencies.');
assertTrue(in_array('POL-005', $genericCodes, true), 'Policy diagnostics should flag generic concept naming.');
assertTrue(in_array('POL-008', $locatorCodes, true), 'Policy diagnostics should flag service locator drift.');
assertTrue(
    ($serviceGraph['structureDiff']['ownership']['changed'] ?? false) === true,
    'Structure diff diagnostics should detect ownership changes after compilation.'
);
assertTrue(
    str_contains($issues, 'POL-004'),
    'Validation should surface policy errors for direct flow-to-flow dependencies.'
);

@rmdir($cacheDir);

echo basename(__FILE__) . " ok\n";
