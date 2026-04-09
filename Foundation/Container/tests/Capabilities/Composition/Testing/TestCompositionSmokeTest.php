<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

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
    public function __construct(public TestingClockContract $clock) {}
}

$composition = TestComposition::create();
$composition->singletonCapability('time', TestingClockContract::class, RealTestingClock::class, exported: true);
$composition->bindFlow('login', LoginFlowEntry::class, LoginFlowEntry::class, entry: true, imports: ['time']);

$container     = $composition->container();
$entry         = $container->make(LoginFlowEntry::class);
$entrySnapshot = $composition->snapshot(LoginFlowEntry::class);

assertInstanceOf(RealTestingClock::class, $entry->clock, 'Flow-only test composition should resolve required imported capabilities.');
assertSame('login', $entrySnapshot['owner']['ownerSlice'] ?? null, 'Snapshot diagnostics should expose the owning flow slice.');
assertSame(['time'], $entrySnapshot['owner']['imports'] ?? [], 'Snapshot diagnostics should expose minimal required imports.');

$composition->override(TestingClockContract::class, FakeTestingClock::class, 'test-double');

$overridden       = $container->make(LoginFlowEntry::class);
$overrideSnapshot = $composition->snapshot(TestingClockContract::class);

assertInstanceOf(FakeTestingClock::class, $overridden->clock, 'Test overrides should replace dependencies cleanly inside the isolated composition.');
assertTrue($overrideSnapshot['overrides'] !== [], 'Test composition snapshots should expose override history.');

$isolated = TestComposition::create();
$isolated->singletonCapability('time', TestingClockContract::class, RealTestingClock::class, exported: true);
$isolated->bindFlow('login', LoginFlowEntry::class, LoginFlowEntry::class, entry: true, imports: ['time']);

$isolatedEntry = $isolated->container()->make(LoginFlowEntry::class);

assertInstanceOf(RealTestingClock::class, $isolatedEntry->clock, 'Fresh test compositions should not leak runtime or override state from previous compositions.');

echo basename(__FILE__) . " ok\n";
