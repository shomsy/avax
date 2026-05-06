<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, 3).'/bootstrap.php';

use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistry;

$registry = new DependencyRegistry();

$registry->bind(abstract: 'payments.gateway', concrete: DateTimeImmutable::class)
    ->asCapability(ownerSlice: 'capability.payments')
    ->asShared()
    ->export()
    ->because(reason: 'Expose one shared gateway to importing slices.')
    ->provenance(provenance: 'ownership-smoke')
    ->concept(concept: 'gateway');

$registry->bind(abstract: 'payments.internal.audit', concrete: ArrayObject::class)
    ->asCapability(ownerSlice: 'capability.payments')
    ->asInternal()
    ->because(reason: 'Keep audit details private to the payments capability.')
    ->concept(concept: 'audit');

$registry->bind(abstract: 'flow.login.handler', concrete: stdClass::class)
    ->asFlow(ownerSlice: 'flow.login')
    ->asPrivate()
    ->import(slices: 'capability.payments')
    ->because(reason: 'Compose the login flow from imported capabilities.')
    ->concept(concept: 'handler');

$registry->bind(abstract: 'flow.login.clock', concrete: DateTimeImmutable::class)
    ->asFlow(ownerSlice: 'flow.login')
    ->asPrivate()
    ->concept(concept: 'clock');

$registry->bind(abstract: 'flow.billing.clock', concrete: DateTimeImmutable::class)
    ->asFlow(ownerSlice: 'flow.billing')
    ->asPrivate()
    ->concept(concept: 'clock');

$ownership = $registry->ownership(abstract: 'payments.gateway');
assertTrue(condition: $ownership !== null, message: 'Ownership metadata should be queryable for registered services.');
assertSame(expected: 'capability.payments', actual: $ownership?->ownerSlice, message: 'Ownership metadata should keep the owner slice.');
assertSame(expected: 'capability', actual: $ownership?->category, message: 'Ownership metadata should keep the category.');
assertSame(expected: 'shared', actual: $ownership?->visibility, message: 'Ownership metadata should keep the visibility.');
assertSame(expected: ['capability.payments'], actual: $registry->sliceManifests()['flow.login']['imports'], message: 'Slice manifests should aggregate declared imports.');
assertSame(expected: ['payments.gateway'], actual: $registry->sliceManifests()['capability.payments']['exports'], message: 'Slice manifests should aggregate explicit exports.');

$access = $registry->accessTo(consumerId: 'flow.login.handler', dependencyId: 'payments.gateway');
assertTrue(condition: $access['allowed'], message: 'Imported shared exports should be accessible across slices.');
assertSame(
    expected: 'dependency is explicitly exported and the consumer slice imports it',
    actual  : $access['reason'],
    message : 'Cross-slice access should explain why it is allowed.',
);

$internalAccess = $registry->accessTo(consumerId: 'flow.login.handler', dependencyId: 'payments.internal.audit');
assertTrue(condition: ! $internalAccess['allowed'], message: 'Internal services should not be accessible across slices.');
assertSame(
    expected: 'internal dependencies cannot be used outside their owning slice',
    actual  : $internalAccess['reason'],
    message : 'Cross-slice access failures should explain the blocked visibility.',
);

$topLevelExport = $registry->topLevelAccessTo(serviceId: 'payments.gateway');
assertTrue(condition: $topLevelExport['allowed'], message: 'Exported shared services should be part of the top-level surface.');

$topLevelInternal = $registry->topLevelAccessTo(serviceId: 'payments.internal.audit');
assertTrue(condition: ! $topLevelInternal['allowed'], message: 'Internal services should stay out of the top-level surface.');

$duplicates = $registry->duplicateConcepts();
assertSame(expected: 'clock', actual: $duplicates[0]['concept'] ?? null, message: 'Duplicate concept reports should retain the repeated concept name.');
assertSame(expected: 2, actual: count(value: $duplicates[0]['services'] ?? []), message: 'Duplicate concept reports should keep every conflicting owner.');

$ownershipMap = $registry->ownershipMap();
assertSame(
    expected: 'ownership-smoke',
    actual  : $ownershipMap['payments.gateway']['provenance'] ?? null,
    message : 'Ownership maps should preserve provenance for diagnostics and compile metadata.',
);

echo basename(path: __FILE__)." ok\n";
