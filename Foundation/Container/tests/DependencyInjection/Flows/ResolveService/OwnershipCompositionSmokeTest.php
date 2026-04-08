<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\Configuration\CreateContainerConfig;
use Avax\Container\Errors\ContainerException;

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

final class ScopedOwnershipState
{
}

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

final class DevOnlyOwnershipProbe
{
}

$container = makeTestContainer(CreateContainerConfig::create(settings: ['app_env' => 'prod']));

$container->singleton(OwnershipGateway::class, SharedOwnershipGateway::class)
    ->asCapability('capability.payments')
    ->asShared()
    ->export()
    ->because('Expose one shared payments gateway to importing flows.')
    ->provenance('ownership-composition-smoke');

$container->bind(InternalAuditTrail::class, InternalAuditTrail::class)
    ->asCapability('capability.payments')
    ->asInternal()
    ->because('Keep audit details internal to the payments capability.');

$container->scoped(ScopedOwnershipState::class, ScopedOwnershipState::class)
    ->asFlow('flow.request')
    ->asInternal()
    ->because('Keep request state inside the active operation scope.');

$container->bind(BillingFlowUsesGateway::class, BillingFlowUsesGateway::class)
    ->asFlow('flow.billing')
    ->asPrivate()
    ->entry()
    ->import('capability.payments')
    ->because('Compose billing flow from an imported payments capability.');

$container->bind(BillingFlowUsesInternalAudit::class, BillingFlowUsesInternalAudit::class)
    ->asFlow('flow.billing')
    ->asPrivate()
    ->entry()
    ->because('This intentionally violates visibility to exercise the validator.');

$container->singleton(SharedOwnershipFacade::class, SharedOwnershipFacade::class)
    ->asCapability('capability.analytics')
    ->asPublic()
    ->because('This intentionally captures scoped state to exercise lifetime validation.');

$container->bind(DevOnlyOwnershipProbe::class, DevOnlyOwnershipProbe::class)
    ->asConfiguration('configuration.dev')
    ->asInternal()
    ->profiles(['dev'])
    ->because('This probe should stay disabled outside the dev environment.');

$container->bind('flow.billing.clock', DateTimeImmutable::class)
    ->asFlow('flow.billing')
    ->asPrivate()
    ->concept('clock');

$container->bind('flow.login.clock', DateTimeImmutable::class)
    ->asFlow('flow.login')
    ->asPrivate()
    ->concept('clock');

$description = $container->describeService(OwnershipGateway::class);
$graph = $container->debugGraph(BillingFlowUsesGateway::class);
$fullGraph = $container->debugGraph();
$validGatewayIssues = $container->validate([BillingFlowUsesGateway::class]);
$invalidAuditIssues = $container->validate([BillingFlowUsesInternalAudit::class]);
$lifetimeIssues = $container->validate([SharedOwnershipFacade::class]);
$profileIssues = $container->validate([DevOnlyOwnershipProbe::class]);

assertSame('capability.payments', $description['ownership']['ownerSlice'] ?? null, 'Service descriptions should expose the owner slice.');
assertSame('shared', $description['ownership']['visibility'] ?? null, 'Service descriptions should expose visibility.');
assertSame(
    'service is shared and explicitly exported',
    $description['topLevelAccess']['reason'] ?? null,
    'Service descriptions should explain why a service is on the top-level surface.'
);
assertSame(
    ['capability.payments'],
    $graph['owner']['imports'] ?? [],
    'Service graph reports should expose declared slice imports.'
);
assertTrue(isset($fullGraph['slices']['capability.payments']), 'Full graph reports should include slice manifests.');
assertTrue(isset($fullGraph['duplicateConcepts'][0]['concept']), 'Full graph reports should include duplicate concept reports.');
assertTrue(in_array(BillingFlowUsesGateway::class, $fullGraph['deadRegistrations'], true) === false, 'Live flow services should not be reported as dead.');
assertSame([], array_values(array_filter(
    $validGatewayIssues,
    static fn(string $issue) : bool => str_contains($issue, BillingFlowUsesGateway::class)
)), 'Imported shared capability dependencies should validate cleanly.');

$invalidAuditText = implode("\n", $invalidAuditIssues);
assertTrue(
    str_contains($invalidAuditText, 'cannot use dependency [' . InternalAuditTrail::class . ']'),
    'Validation should block illegal internal cross-slice dependencies.'
);

$lifetimeText = implode("\n", $lifetimeIssues);
assertTrue(
    str_contains($lifetimeText, 'captures scoped dependency [' . ScopedOwnershipState::class . ']'),
    'Validation should detect shared-to-scoped lifetime capture.'
);

$profileText = implode("\n", $profileIssues);
assertTrue(
    str_contains($profileText, 'environment [prod] is not in [dev]'),
    'Validation should explain environment/profile mismatches.'
);

assertSame(
    'shared-gateway',
    $container->get(OwnershipGateway::class)->label(),
    'Exported shared capabilities should still resolve normally.'
);
assertSame(
    'shared-gateway',
    $container->get(BillingFlowUsesGateway::class)->gateway->label(),
    'Importing flows should resolve exported shared capabilities.'
);

assertThrows(
    ContainerException::class,
    static function () use ($container) : void {
        $container->get(InternalAuditTrail::class);
    },
    'Top-level internal services should be blocked from direct resolution.'
);

assertThrows(
    ContainerException::class,
    static function () use ($container) : void {
        $container->get(BillingFlowUsesInternalAudit::class);
    },
    'Illegal cross-slice runtime dependencies should fail fast during resolution.'
);

echo basename(__FILE__) . " ok\n";
