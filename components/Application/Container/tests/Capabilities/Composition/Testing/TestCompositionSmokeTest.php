<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 3) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\Testing\TestComposition;

interface TestingClockContract
{
    public function now() : string;
}

final class RealTestingClock implements TestingClockContract
{
    public function now() : string
    {
        return 'real';
    }
}

final class FakeTestingClock implements TestingClockContract
{
    public function now() : string
    {
        return 'fake';
    }
}

final class LoginFlowEntry
{
    public TestingClockContract $clock;

    public function __construct(TestingClockContract $clock) { $this->clock = $clock; }
}

$composition = TestComposition::create();
$composition->singletonCapability(slice: 'time', abstract: TestingClockContract::class, concrete: RealTestingClock::class, exported: true);
$composition->bindFlow(slice: 'login', abstract: LoginFlowEntry::class, concrete: LoginFlowEntry::class, entry: true, imports: ['time']);

$container     = $composition->container();
$entry         = $container->make(abstract: LoginFlowEntry::class);
$entrySnapshot = $composition->snapshot(serviceId: LoginFlowEntry::class);

assertInstanceOf(expectedClass: RealTestingClock::class, value: $entry->clock, message: 'Flow-only test composition should resolve required imported capabilities.');
assertSame(expected: 'login', actual: $entrySnapshot['owner']['ownerSlice'] ?? null, message: 'Snapshot diagnostics should expose the owning flow slice.');
assertSame(expected: ['time'], actual: $entrySnapshot['owner']['imports'] ?? [], message: 'Snapshot diagnostics should expose minimal required imports.');

$composition->override(abstract: TestingClockContract::class, concrete: FakeTestingClock::class, source: 'test-double');

$overridden       = $container->make(abstract: LoginFlowEntry::class);
$overrideSnapshot = $composition->snapshot(serviceId: TestingClockContract::class);

assertInstanceOf(expectedClass: FakeTestingClock::class, value: $overridden->clock, message: 'Test overrides should replace dependencies cleanly inside the isolated composition.');
assertTrue(condition: $overrideSnapshot['overrides'] !== [], message: 'Test composition snapshots should expose override history.');

$isolated = TestComposition::create();
$isolated->singletonCapability(slice: 'time', abstract: TestingClockContract::class, concrete: RealTestingClock::class, exported: true);
$isolated->bindFlow(slice: 'login', abstract: LoginFlowEntry::class, concrete: LoginFlowEntry::class, entry: true, imports: ['time']);

$isolatedEntry = $isolated->container()->make(abstract: LoginFlowEntry::class);

assertInstanceOf(expectedClass: RealTestingClock::class, value: $isolatedEntry->clock, message: 'Fresh test compositions should not leak runtime or override state from previous compositions.');

echo basename(path: __FILE__) . " ok\n";
