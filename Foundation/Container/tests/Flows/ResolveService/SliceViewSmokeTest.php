<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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

final class SliceInternalAudit {}

final class SliceBillingEntry
{
    public SlicePaymentGateway $gateway;

    public function __construct(SlicePaymentGateway $gateway) { $this->gateway = $gateway; }
}

final class SliceBillingHelper {}

final class SliceLoginSecret {}

final class SliceConfigProbe {}

final class SliceClock {}

final class SliceBillingOwnedService {}

$container = makeTestContainer();

$container->singleton(abstract: SlicePaymentGateway::class, concrete: ExportedSliceGateway::class)
    ->asCapability(ownerSlice: 'capability.payments')
    ->asShared()
    ->export();

$container->bind(abstract: SliceInternalAudit::class, concrete: SliceInternalAudit::class)
    ->asCapability(ownerSlice: 'capability.payments')
    ->asInternal();

$container->bind(abstract: SliceBillingEntry::class, concrete: SliceBillingEntry::class)
    ->asFlow(ownerSlice: 'flow.billing')
    ->asPrivate()
    ->entry()
    ->import(slices: 'capability.payments');

$container->bind(abstract: SliceBillingHelper::class, concrete: SliceBillingHelper::class)
    ->asFlow(ownerSlice: 'flow.billing')
    ->asInternal();

$container->bind(abstract: SliceLoginSecret::class, concrete: SliceLoginSecret::class)
    ->asFlow(ownerSlice: 'flow.login')
    ->asPrivate();

$container->bind(abstract: SliceConfigProbe::class, concrete: SliceConfigProbe::class)
    ->asConfiguration(ownerSlice: 'configuration.runtime')
    ->asInternal();

$container->bind(abstract: SliceClock::class, concrete: SliceClock::class)
    ->asFoundation(ownerSlice: 'foundation.time')
    ->asInternal();

$billing       = $container->forSlice(slice: 'flow.billing');
$payments      = $container->forSlice(slice: 'capability.payments');
$configuration = $container->forSlice(slice: 'configuration.runtime');
$foundation    = $container->forSlice(slice: 'foundation.time');

assertTrue(condition: $billing->has(id: SliceBillingEntry::class), message: 'Flow slice views should expose their entry units.');
assertTrue(condition: $billing->has(id: SliceBillingHelper::class), message: 'Flow slice views should expose their internal units.');
assertTrue(condition: $billing->has(id: SlicePaymentGateway::class), message: 'Flow slice views should expose imported shared exports.');
assertTrue(condition: ! $billing->has(id: SliceInternalAudit::class), message: 'Flow slice views should not expose internal capability units.');
assertTrue(condition: ! $billing->has(id: SliceLoginSecret::class), message: 'Flow slice views should not expose private units from another flow.');
assertSame(expected: 'payments', actual: $billing->get(id: SliceBillingEntry::class)->gateway->label(), message: 'Flow slice views should resolve their imported dependencies.');

assertTrue(condition: $payments->has(id: SliceInternalAudit::class), message: 'Capability slice views should expose their internal implementation units.');
assertTrue(condition: ! $payments->has(id: SliceBillingHelper::class), message: 'Capability slice views should not expose flow-private internals.');

assertTrue(condition: $configuration->has(id: SliceConfigProbe::class), message: 'Configuration slice views should expose their own units.');
assertTrue(condition: ! $configuration->has(id: SliceBillingHelper::class), message: 'Configuration slice views should not expose unrelated flow internals.');

assertTrue(condition: $foundation->has(id: SliceClock::class), message: 'Foundation slice views should expose their own units.');
assertTrue(condition: ! $foundation->has(id: SliceInternalAudit::class), message: 'Foundation slice views should not expose capability internals.');

$billingGraph        = $billing->debugGraph();
$billingServiceGraph = $billing->debugGraph(id: SliceInternalAudit::class);
$billingDescription  = $billing->describeService(id: SliceInternalAudit::class);
$globalBillingGraph  = $container->debugGraph(id: 'flow.billing');
$billingSlice        = $billing->debugSlice();
$billingImports      = $billing->debugImports();
$paymentsExports     = $payments->debugExports();
$violations          = $billing->debugVisibilityViolations(serviceIds: [SliceInternalAudit::class]);
$billingGovernance   = $billing->debugGovernance();
$billingArchitecture = $billing->debugArchitecture();

assertSame(expected: 'flow.billing', actual: $billingGraph['sliceView']['slice'] ?? null, message: 'Slice graph diagnostics should expose the active slice view.');
assertSame(expected: 'flow.billing', actual: $globalBillingGraph['sliceView']['slice'] ?? null, message: 'Global graph diagnostics should be able to pivot into one slice view.');
assertSame(expected: 'flow.billing', actual: $billingSlice['slice'] ?? null, message: 'debugSlice() should expose the active slice manifest.');
assertTrue(
    condition: in_array(SliceBillingEntry::class, array_column($billingGraph['sliceView']['visible'] ?? [], 'serviceId'), true),
    message  : 'Slice graph diagnostics should list visible services.'
);
assertTrue(
    condition: in_array(SlicePaymentGateway::class, array_column($billingSlice['visible'] ?? [], 'serviceId'), true),
    message  : 'Slice diagnostics should expose imported shared exports.'
);
assertTrue(
    condition: in_array(SliceInternalAudit::class, array_column($billingGraph['hiddenServices'] ?? [], 'serviceId'), true),
    message  : 'Slice graph diagnostics should list hidden services with reasons.'
);
assertSame(expected: ['capability.payments'], actual: array_column($billingImports['imports'] ?? [], 'slice'), message: 'debugImports() should expose imported slice manifests.');
assertSame(expected: [SlicePaymentGateway::class], actual: array_column($paymentsExports['exports'] ?? [], 'serviceId'), message: 'debugExports() should expose explicitly exported units.');
assertTrue(
    condition: in_array(SliceInternalAudit::class, array_column($violations['violations'] ?? [], 'dependencyId'), true),
    message  : 'debugVisibilityViolations() should report blocked slice access attempts.'
);
assertSame(expected: 'flow.billing', actual: $billingGovernance['sliceView']['slice'] ?? null, message: 'Slice governance diagnostics should expose the active slice view.');
assertSame(expected: 'flow.billing', actual: $billingArchitecture['sliceView']['slice'] ?? null, message: 'Slice architecture diagnostics should expose the active slice view.');
assertSame(expected: false, actual: $billingServiceGraph['viewAccess']['allowed'] ?? true, message: 'Per-service slice graph diagnostics should explain blocked access.');
assertSame(expected: false, actual: $billingDescription['viewAccess']['allowed'] ?? true, message: 'Slice-aware descriptions should expose blocked view access.');

$ownedRegistration = $billing->bind(abstract: SliceBillingOwnedService::class, concrete: SliceBillingOwnedService::class);

assertSame(expected: 'flow.billing', actual: $ownedRegistration->metadata->ownerSlice, message: 'Strict slice views should stamp new registrations with the active slice owner.');
assertSame(expected: 'flow', actual: $ownedRegistration->metadata->category, message: 'Strict slice views should stamp new registrations with the active slice category.');

assertThrows(
    expectedClass: LogicException::class,
    callback     : static function () use ($ownedRegistration) : void {
        $ownedRegistration->asCapability(ownerSlice: 'capability.payments');
    },
    message      : 'Strict slice views should lock registration ownership against cross-slice mutation.'
);

assertThrows(
    expectedClass: InvalidArgumentException::class,
    callback     : static function () use ($billing) : void {
        $billing->tag(abstracts: SlicePaymentGateway::class, tags: 'illegal');
    },
    message      : 'Strict slice views should block mutating imported services.'
);

assertThrows(
    expectedClass: InvalidArgumentException::class,
    callback     : static function () use ($billing) : void {
        $billing->alias(alias: 'billing.gateway', abstract: SlicePaymentGateway::class);
    },
    message      : 'Strict slice views should block global alias mutation.'
);

assertThrows(
    expectedClass: InvalidArgumentException::class,
    callback     : static function () use ($billing) : void {
        $billing->flushCompiled();
    },
    message      : 'Strict slice views should block global compiled artifact mutation.'
);

assertThrows(
/**
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */ expectedClass: ContainerException::class,
    callback     : static function () use ($billing) : void {
        $billing->get(id: SliceInternalAudit::class);
    },
    message      : 'Flow slice views should block direct resolution of hidden capability internals.'
);

echo basename(__FILE__) . " ok\n";
