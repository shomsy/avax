<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
interface SlicePaymentGateway
{
    public function label() : string;
}

final class ExportedSliceGateway implements SlicePaymentGateway
{
    public function label() : string
    {
        return 'payments';
    }
}

final class SliceInternalAudit
{
}

final class SliceBillingEntry
{
    public function __construct(public SlicePaymentGateway $gateway)
    {
    }
}

final class SliceBillingHelper
{
}

final class SliceLoginSecret
{
}

final class SliceConfigProbe
{
}

final class SliceClock
{
}

final class SliceBillingOwnedService
{
}

$container = makeTestContainer();

$container->singleton(SlicePaymentGateway::class, ExportedSliceGateway::class)
    ->asCapability('capability.payments')
    ->asShared()
    ->export();

$container->bind(SliceInternalAudit::class, SliceInternalAudit::class)
    ->asCapability('capability.payments')
    ->asInternal();

$container->bind(SliceBillingEntry::class, SliceBillingEntry::class)
    ->asFlow('flow.billing')
    ->asPrivate()
    ->entry()
    ->import('capability.payments');

$container->bind(SliceBillingHelper::class, SliceBillingHelper::class)
    ->asFlow('flow.billing')
    ->asInternal();

$container->bind(SliceLoginSecret::class, SliceLoginSecret::class)
    ->asFlow('flow.login')
    ->asPrivate();

$container->bind(SliceConfigProbe::class, SliceConfigProbe::class)
    ->asConfiguration('configuration.runtime')
    ->asInternal();

$container->bind(SliceClock::class, SliceClock::class)
    ->asFoundation('foundation.time')
    ->asInternal();

$billing = $container->forSlice('flow.billing');
$payments = $container->forSlice('capability.payments');
$configuration = $container->forSlice('configuration.runtime');
$foundation = $container->forSlice('foundation.time');

assertTrue($billing->has(SliceBillingEntry::class), 'Flow slice views should expose their entry units.');
assertTrue($billing->has(SliceBillingHelper::class), 'Flow slice views should expose their internal units.');
assertTrue($billing->has(SlicePaymentGateway::class), 'Flow slice views should expose imported shared exports.');
assertTrue(! $billing->has(SliceInternalAudit::class), 'Flow slice views should not expose internal capability units.');
assertTrue(! $billing->has(SliceLoginSecret::class), 'Flow slice views should not expose private units from another flow.');
assertSame('payments', $billing->get(SliceBillingEntry::class)->gateway->label(), 'Flow slice views should resolve their imported dependencies.');

assertTrue($payments->has(SliceInternalAudit::class), 'Capability slice views should expose their internal implementation units.');
assertTrue(! $payments->has(SliceBillingHelper::class), 'Capability slice views should not expose flow-private internals.');

assertTrue($configuration->has(SliceConfigProbe::class), 'Configuration slice views should expose their own units.');
assertTrue(! $configuration->has(SliceBillingHelper::class), 'Configuration slice views should not expose unrelated flow internals.');

assertTrue($foundation->has(SliceClock::class), 'Foundation slice views should expose their own units.');
assertTrue(! $foundation->has(SliceInternalAudit::class), 'Foundation slice views should not expose capability internals.');

$billingGraph = $billing->debugGraph();
$billingServiceGraph = $billing->debugGraph(SliceInternalAudit::class);
$billingDescription = $billing->describeService(SliceInternalAudit::class);
$globalBillingGraph = $container->debugGraph('flow.billing');
$billingSlice = $billing->debugSlice();
$billingImports = $billing->debugImports();
$paymentsExports = $payments->debugExports();
$violations = $billing->debugVisibilityViolations([SliceInternalAudit::class]);
$billingGovernance = $billing->debugGovernance();
$billingArchitecture = $billing->debugArchitecture();

assertSame('flow.billing', $billingGraph['sliceView']['slice'] ?? null, 'Slice graph diagnostics should expose the active slice view.');
assertSame('flow.billing', $globalBillingGraph['sliceView']['slice'] ?? null, 'Global graph diagnostics should be able to pivot into one slice view.');
assertSame('flow.billing', $billingSlice['slice'] ?? null, 'debugSlice() should expose the active slice manifest.');
assertTrue(
    in_array(SliceBillingEntry::class, array_column($billingGraph['sliceView']['visible'] ?? [], 'serviceId'), true),
    'Slice graph diagnostics should list visible services.'
);
assertTrue(
    in_array(SlicePaymentGateway::class, array_column($billingSlice['visible'] ?? [], 'serviceId'), true),
    'Slice diagnostics should expose imported shared exports.'
);
assertTrue(
    in_array(SliceInternalAudit::class, array_column($billingGraph['hiddenServices'] ?? [], 'serviceId'), true),
    'Slice graph diagnostics should list hidden services with reasons.'
);
assertSame(['capability.payments'], array_column($billingImports['imports'] ?? [], 'slice'), 'debugImports() should expose imported slice manifests.');
assertSame([SlicePaymentGateway::class], array_column($paymentsExports['exports'] ?? [], 'serviceId'), 'debugExports() should expose explicitly exported units.');
assertTrue(
    in_array(SliceInternalAudit::class, array_column($violations['violations'] ?? [], 'dependencyId'), true),
    'debugVisibilityViolations() should report blocked slice access attempts.'
);
assertSame('flow.billing', $billingGovernance['sliceView']['slice'] ?? null, 'Slice governance diagnostics should expose the active slice view.');
assertSame('flow.billing', $billingArchitecture['sliceView']['slice'] ?? null, 'Slice architecture diagnostics should expose the active slice view.');
assertSame(false, $billingServiceGraph['viewAccess']['allowed'] ?? true, 'Per-service slice graph diagnostics should explain blocked access.');
assertSame(false, $billingDescription['viewAccess']['allowed'] ?? true, 'Slice-aware descriptions should expose blocked view access.');

$ownedRegistration = $billing->bind(SliceBillingOwnedService::class, SliceBillingOwnedService::class);

assertSame('flow.billing', $ownedRegistration->metadata->ownerSlice, 'Strict slice views should stamp new registrations with the active slice owner.');
assertSame('flow', $ownedRegistration->metadata->category, 'Strict slice views should stamp new registrations with the active slice category.');

assertThrows(
    LogicException::class,
    static function () use ($ownedRegistration) : void {
        $ownedRegistration->asCapability('capability.payments');
    },
    'Strict slice views should lock registration ownership against cross-slice mutation.'
);

assertThrows(
    InvalidArgumentException::class,
    static function () use ($billing) : void {
        $billing->tag(SlicePaymentGateway::class, 'illegal');
    },
    'Strict slice views should block mutating imported services.'
);

assertThrows(
    InvalidArgumentException::class,
    static function () use ($billing) : void {
        $billing->alias('billing.gateway', SlicePaymentGateway::class);
    },
    'Strict slice views should block global alias mutation.'
);

assertThrows(
    InvalidArgumentException::class,
    static function () use ($billing) : void {
        $billing->flushCompiled();
    },
    'Strict slice views should block global compiled artifact mutation.'
);

assertThrows(
/**
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */ ContainerException::class,
    static function () use ($billing) : void {
        $billing->get(SliceInternalAudit::class);
    },
    'Flow slice views should block direct resolution of hidden capability internals.'
);

echo basename(__FILE__) . " ok\n";
