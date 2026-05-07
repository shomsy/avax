<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Components\Application\Container\System\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\System\ContainerInterface;

final class PolicyAndStructureDiffSmokeTest {}

final class PolicyDependencyB {}

final class PolicyDependencyC {}

final class PolicyDependencyD {}

final class PolicyDependencyE {}

final class PolicyDependencyF {}

final readonly class OverInjectedPolicyService {}

final class OtherFlowLocal {}

final class FlowToFlowEntry
{
    public function __construct(public OtherFlowLocal $otherFlowLocal) {}
}

final class GenericHelperService {}

final class StructureDiffService {}

final class LocatorDriftService
{
    public ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}

$cacheDir  = sys_get_temp_dir() . '/container-policy-diff-' . uniqid(prefix: '', more_entropy: true);
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
$issues       = implode(separator: "\n", array: $container->validate(serviceIds: [
                                                                                     OverInjectedPolicyService::class,
                                                                                     FlowToFlowEntry::class,
                                                                                     GenericHelperService::class,
                                                                                     StructureDiffService::class,
                                                                                     LocatorDriftService::class,
                                                                                 ]));

$overInjectedCodes = array_column(array: $graph['policyFindings'][OverInjectedPolicyService::class] ?? [], column_key: 'code');
$flowCodes         = array_column(array: $graph['policyFindings'][FlowToFlowEntry::class] ?? [], column_key: 'code');
$genericCodes      = array_column(array: $graph['policyFindings'][GenericHelperService::class] ?? [], column_key: 'code');
$locatorCodes      = array_column(array: $graph['policyFindings'][LocatorDriftService::class] ?? [], column_key: 'code');

assertTrue(condition: in_array(needle: 'POL-001', haystack: $overInjectedCodes, strict: true), message: 'Policy diagnostics should flag over-injected constructors.');
assertTrue(condition: in_array(needle: 'POL-004', haystack: $flowCodes, strict: true), message: 'Policy diagnostics should flag direct flow-to-flow dependencies.');
assertTrue(condition: in_array(needle: 'POL-005', haystack: $genericCodes, strict: true), message: 'Policy diagnostics should flag generic concept naming.');
assertTrue(condition: in_array(needle: 'POL-008', haystack: $locatorCodes, strict: true), message: 'Policy diagnostics should flag service locator drift.');
assertTrue(
    condition: ($serviceGraph['structureDiff']['ownership']['changed'] ?? false) === true,
    message  : 'Structure diff diagnostics should detect ownership changes after compilation.',
);
assertTrue(
    condition: str_contains(haystack: $issues, needle: 'POL-004'),
    message  : 'Validation should surface policy errors for direct flow-to-flow dependencies.',
);

rmdir(directory: $cacheDir);

echo basename(path: __FILE__) . " ok\n";
