<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 2) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

interface OwnershipGateway
{
    public function label() : string;
}

final class SharedOwnershipGateway implements OwnershipGateway
{
    public function label() : string
    {
        return 'shared-gateway';
    }
}

final class InternalAuditTrail
{
    public function label() : string
    {
        return 'internal-audit';
    }
}

final class ScopedOwnershipState {}

final class BillingFlowUsesGateway
{
    public function __construct(public OwnershipGateway $gateway)
    {
    }
}

final class BillingFlowUsesInternalAudit
{
    public function __construct(public InternalAuditTrail $audit)
    {
    }
}

final class SharedOwnershipFacade
{
    public function __construct(public ScopedOwnershipState $state)
    {
    }
}

final class DevOnlyOwnershipProbe {}

$container = makeTestContainer(config: CreateContainerConfig::create(settings: ['app_env' => 'prod']));

$container->singleton(abstract: OwnershipGateway::class, concrete: SharedOwnershipGateway::class)
    ->asCapability(ownerSlice: 'capability.payments')
    ->asShared()
    ->export()
    ->because(reason: 'Expose one shared payments gateway to importing flows.')
    ->provenance(provenance: 'ownership-composition-smoke');

$container->bind(abstract: InternalAuditTrail::class, concrete: InternalAuditTrail::class)
    ->asCapability(ownerSlice: 'capability.payments')
    ->asInternal()
    ->because(reason: 'Keep audit details internal to the payments capability.');

$container->scoped(abstract: ScopedOwnershipState::class, concrete: ScopedOwnershipState::class)
    ->asFlow(ownerSlice: 'flow.request')
    ->asInternal()
    ->because(reason: 'Keep request state inside the active operation scope.');

$container->bind(abstract: BillingFlowUsesGateway::class, concrete: BillingFlowUsesGateway::class)
    ->asFlow(ownerSlice: 'flow.billing')
    ->asPrivate()
    ->entry()
    ->import(slices: 'capability.payments')
    ->because(reason: 'Compose billing flow from an imported payments capability.');

$container->bind(abstract: BillingFlowUsesInternalAudit::class, concrete: BillingFlowUsesInternalAudit::class)
    ->asFlow(ownerSlice: 'flow.billing')
    ->asPrivate()
    ->entry()
    ->because(reason: 'This intentionally violates visibility to exercise the validator.');

$container->singleton(abstract: SharedOwnershipFacade::class, concrete: SharedOwnershipFacade::class)
    ->asCapability(ownerSlice: 'capability.analytics')
    ->asPublic()
    ->because(reason: 'This intentionally captures scoped state to exercise lifetime validation.');

$container->bind(abstract: DevOnlyOwnershipProbe::class, concrete: DevOnlyOwnershipProbe::class)
    ->asConfiguration(ownerSlice: 'configuration.dev')
    ->asInternal()
    ->profiles(profiles: ['dev'])
    ->because(reason: 'This probe should stay disabled outside the dev environment.');

$container->bind(abstract: 'flow.billing.clock', concrete: DateTimeImmutable::class)
    ->asFlow(ownerSlice: 'flow.billing')
    ->asPrivate()
    ->concept(concept: 'clock');

$container->bind(abstract: 'flow.login.clock', concrete: DateTimeImmutable::class)
    ->asFlow(ownerSlice: 'flow.login')
    ->asPrivate()
    ->concept(concept: 'clock');

$description    = $container->describeService(id: OwnershipGateway::class);
$graph          = $container->debugGraph(id: BillingFlowUsesGateway::class);
$fullGraph      = $container->debugGraph();
$validGatewayIssues = $container->validate(serviceIds: [BillingFlowUsesGateway::class]);
$invalidAuditIssues = $container->validate(serviceIds: [BillingFlowUsesInternalAudit::class]);
$lifetimeIssues = $container->validate(serviceIds: [SharedOwnershipFacade::class]);
$profileIssues  = $container->validate(serviceIds: [DevOnlyOwnershipProbe::class]);

assertSame(expected: 'capability.payments', actual: $description['ownership']['ownerSlice'] ?? null, message: 'Service descriptions should expose the owner slice.');
assertSame(expected: 'shared', actual: $description['ownership']['visibility'] ?? null, message: 'Service descriptions should expose visibility.');
assertSame(
    expected: 'service is shared and explicitly exported',
    actual  : $description['topLevelAccess']['reason'] ?? null,
    message : 'Service descriptions should explain why a service is on the top-level surface.',
);
assertSame(
    expected: ['capability.payments'],
    actual  : $graph['owner']['imports'] ?? [],
    message : 'Service graph reports should expose declared slice imports.',
);
assertTrue(condition: isset($fullGraph['slices']['capability.payments']), message: 'Full graph reports should include slice manifests.');
assertTrue(condition: isset($fullGraph['duplicateConcepts'][0]['concept']), message: 'Full graph reports should include duplicate concept reports.');
assertTrue(condition: in_array(needle: BillingFlowUsesGateway::class, haystack: $fullGraph['deadRegistrations'], strict: true) === false, message: 'Live flow services should not be reported as dead.');
array_filter(
    array   : $validGatewayIssues,
    callback: static fn (string $issue) : bool => str_contains(haystack: $issue, needle: BillingFlowUsesGateway::class),
)
    |> array_values(...)
    |> (static fn ($x) => assertSame(expected: [], actual: $x, message: 'Imported shared capability dependencies should validate cleanly.'));

$invalidAuditText = implode(separator: "\n", array: $invalidAuditIssues);
assertTrue(
    condition: str_contains(haystack: $invalidAuditText, needle: 'cannot use dependency [' . InternalAuditTrail::class . ']'),
    message  : 'Validation should block illegal internal cross-slice dependencies.',
);

$lifetimeText = implode(separator: "\n", array: $lifetimeIssues);
assertTrue(
    condition: str_contains(haystack: $lifetimeText, needle: 'captures scoped dependency [' . ScopedOwnershipState::class . ']'),
    message  : 'Validation should detect shared-to-scoped lifetime capture.',
);

$profileText = implode(separator: "\n", array: $profileIssues);
assertTrue(
    condition: str_contains(haystack: $profileText, needle: 'environment [prod] is not in [dev]'),
    message  : 'Validation should explain environment/profile mismatches.',
);

assertSame(
    expected: 'shared-gateway',
    actual  : $container->get(id: OwnershipGateway::class)->label(),
    message : 'Exported shared capabilities should still resolve normally.',
);
assertSame(
    expected: 'shared-gateway',
    actual  : $container->get(id: BillingFlowUsesGateway::class)->gateway->label(),
    message : 'Importing flows should resolve exported shared capabilities.',
);

assertThrows(
/**
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */ /**
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */ /**
 * @throws Throwable
 */ /**
 * @throws Throwable
 */
    expectedClass: ContainerException::class,
    callback     : static function () use ($container) : void {
        $container->get(id: InternalAuditTrail::class);
    },
    message      : 'Top-level internal services should be blocked from direct resolution.',
);

assertThrows(
/**
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */ /**
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */ /**
 * @throws Throwable
 */ /**
 * @throws Throwable
 */
    expectedClass: ContainerException::class,
    callback     : static function () use ($container) : void {
        $container->get(id: BillingFlowUsesInternalAudit::class);
    },
    message      : 'Illegal cross-slice runtime dependencies should fail fast during resolution.',
);

echo basename(path: __FILE__) . " ok\n";
