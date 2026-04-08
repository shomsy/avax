<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistry;

$registry = new ServiceRegistry();

$registry->bind('payments.gateway', DateTimeImmutable::class)
    ->asCapability('capability.payments')
    ->asShared()
    ->export()
    ->because('Expose one shared gateway to importing slices.')
    ->provenance('ownership-smoke')
    ->concept('gateway');

$registry->bind('payments.internal.audit', ArrayObject::class)
    ->asCapability('capability.payments')
    ->asInternal()
    ->because('Keep audit details private to the payments capability.')
    ->concept('audit');

$registry->bind('flow.login.handler', stdClass::class)
    ->asFlow('flow.login')
    ->asPrivate()
    ->import('capability.payments')
    ->because('Compose the login flow from imported capabilities.')
    ->concept('handler');

$registry->bind('flow.login.clock', DateTimeImmutable::class)
    ->asFlow('flow.login')
    ->asPrivate()
    ->concept('clock');

$registry->bind('flow.billing.clock', DateTimeImmutable::class)
    ->asFlow('flow.billing')
    ->asPrivate()
    ->concept('clock');

$ownership = $registry->ownership('payments.gateway');
assertTrue($ownership !== null, 'Ownership metadata should be queryable for registered services.');
assertSame('capability.payments', $ownership?->ownerSlice, 'Ownership metadata should keep the owner slice.');
assertSame('capability', $ownership?->category, 'Ownership metadata should keep the category.');
assertSame('shared', $ownership?->visibility, 'Ownership metadata should keep the visibility.');
assertSame(['capability.payments'], $registry->sliceManifests()['flow.login']['imports'], 'Slice manifests should aggregate declared imports.');
assertSame(['payments.gateway'], $registry->sliceManifests()['capability.payments']['exports'], 'Slice manifests should aggregate explicit exports.');

$access = $registry->accessTo('flow.login.handler', 'payments.gateway');
assertTrue($access['allowed'], 'Imported shared exports should be accessible across slices.');
assertSame(
    'dependency is explicitly exported and the consumer slice imports it',
    $access['reason'],
    'Cross-slice access should explain why it is allowed.'
);

$internalAccess = $registry->accessTo('flow.login.handler', 'payments.internal.audit');
assertTrue(! $internalAccess['allowed'], 'Internal services should not be accessible across slices.');
assertSame(
    'internal dependencies cannot be used outside their owning slice',
    $internalAccess['reason'],
    'Cross-slice access failures should explain the blocked visibility.'
);

$topLevelExport = $registry->topLevelAccessTo('payments.gateway');
assertTrue($topLevelExport['allowed'], 'Exported shared services should be part of the top-level surface.');

$topLevelInternal = $registry->topLevelAccessTo('payments.internal.audit');
assertTrue(! $topLevelInternal['allowed'], 'Internal services should stay out of the top-level surface.');

$duplicates = $registry->duplicateConcepts();
assertSame('clock', $duplicates[0]['concept'] ?? null, 'Duplicate concept reports should retain the repeated concept name.');
assertSame(2, count($duplicates[0]['services'] ?? []), 'Duplicate concept reports should keep every conflicting owner.');

$ownershipMap = $registry->ownershipMap();
assertSame(
    'ownership-smoke',
    $ownershipMap['payments.gateway']['provenance'] ?? null,
    'Ownership maps should preserve provenance for diagnostics and compile metadata.'
);

echo basename(__FILE__) . " ok\n";
