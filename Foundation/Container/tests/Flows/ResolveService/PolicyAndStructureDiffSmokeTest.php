<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\ContainerInterface;

final class PolicyDependencyA {}

final class PolicyDependencyB {}

final class PolicyDependencyC {}

final class PolicyDependencyD {}

final class PolicyDependencyE {}

final class PolicyDependencyF {}

final class OverInjectedPolicyService
{
    public function __construct(
        PolicyDependencyA $a,
        PolicyDependencyB $b,
        PolicyDependencyC $c,
        PolicyDependencyD $d,
        PolicyDependencyE $e,
        PolicyDependencyF $f
    ) {}
}

final class OtherFlowLocal {}

final class FlowToFlowEntry
{
    public OtherFlowLocal $local;

    public function __construct(OtherFlowLocal $local) { $this->local = $local; }
}

final class GenericHelperService {}

final class StructureDiffService {}

final class LocatorDriftService
{
    public ContainerInterface $container;

    public function __construct(ContainerInterface $container) { $this->container = $container; }
}

$cacheDir  = sys_get_temp_dir() . '/container-policy-diff-' . uniqid('', true);
$config    = CreateContainerConfig::create(cacheDir: $cacheDir);
$container = makeTestContainer(config: $config);

$container->bind(abstract: PolicyDependencyA::class, concrete: PolicyDependencyA::class);
$container->bind(abstract: PolicyDependencyB::class, concrete: PolicyDependencyB::class);
$container->bind(abstract: PolicyDependencyC::class, concrete: PolicyDependencyC::class);
$container->bind(abstract: PolicyDependencyD::class, concrete: PolicyDependencyD::class);
$container->bind(abstract: PolicyDependencyE::class, concrete: PolicyDependencyE::class);
$container->bind(abstract: PolicyDependencyF::class, concrete: PolicyDependencyF::class);
$container->singleton(abstract: OverInjectedPolicyService::class, concrete: OverInjectedPolicyService::class)
    ->asCapability(ownerSlice: 'checkout.policy')
    ->asShared()
    ->export();
$container->bind(abstract: OtherFlowLocal::class, concrete: OtherFlowLocal::class)
    ->asFlow(ownerSlice: 'read-user')
    ->asPrivate();
$container->bind(abstract: FlowToFlowEntry::class, concrete: FlowToFlowEntry::class)
    ->asFlow(ownerSlice: 'login')
    ->asPrivate()
    ->entry();
$container->singleton(abstract: GenericHelperService::class, concrete: GenericHelperService::class)
    ->asCapability(ownerSlice: 'misc.naming')
    ->asShared()
    ->export()
    ->concept(concept: 'helper');
$container->singleton(abstract: StructureDiffService::class, concrete: StructureDiffService::class)
    ->asCapability(ownerSlice: 'billing')
    ->asShared()
    ->export();
$container->bind(abstract: LocatorDriftService::class, concrete: LocatorDriftService::class)
    ->asCapability(ownerSlice: 'billing')
    ->asShared()
    ->export();

$container->compileContainer(serviceIds: [StructureDiffService::class]);
$container->singleton(abstract: StructureDiffService::class, concrete: StructureDiffService::class)
    ->asFoundation(ownerSlice: 'foundation.diff')
    ->asPublic()
    ->concept(concept: 'structure.diff');

$graph        = $container->debugGraph();
$serviceGraph = $container->debugGraph(id: StructureDiffService::class);
$issues       = implode("\n", $container->validate(serviceIds: [
                                                                   OverInjectedPolicyService::class,
                                                                   FlowToFlowEntry::class,
                                                                   GenericHelperService::class,
                                                                   StructureDiffService::class,
                                                                   LocatorDriftService::class,
                                                               ]));

$overInjectedCodes = array_column($graph['policyFindings'][OverInjectedPolicyService::class] ?? [], 'code');
$flowCodes         = array_column($graph['policyFindings'][FlowToFlowEntry::class] ?? [], 'code');
$genericCodes      = array_column($graph['policyFindings'][GenericHelperService::class] ?? [], 'code');
$locatorCodes      = array_column($graph['policyFindings'][LocatorDriftService::class] ?? [], 'code');

assertTrue(condition: in_array('POL-001', $overInjectedCodes, true), message: 'Policy diagnostics should flag over-injected constructors.');
assertTrue(condition: in_array('POL-004', $flowCodes, true), message: 'Policy diagnostics should flag direct flow-to-flow dependencies.');
assertTrue(condition: in_array('POL-005', $genericCodes, true), message: 'Policy diagnostics should flag generic concept naming.');
assertTrue(condition: in_array('POL-008', $locatorCodes, true), message: 'Policy diagnostics should flag service locator drift.');
assertTrue(
    condition: ($serviceGraph['structureDiff']['ownership']['changed'] ?? false) === true,
    message  : 'Structure diff diagnostics should detect ownership changes after compilation.'
);
assertTrue(
    condition: str_contains($issues, 'POL-004'),
    message  : 'Validation should surface policy errors for direct flow-to-flow dependencies.'
);

@rmdir($cacheDir);

echo basename(__FILE__) . " ok\n";
