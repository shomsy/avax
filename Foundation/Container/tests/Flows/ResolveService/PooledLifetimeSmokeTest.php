<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Runtime\Scopes\DisposableInterface;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ResettableInterface;

final class PooledLifetimeSequence
{
    public static int $created = 0;
    public static int $reset = 0;
    public static int $disposed = 0;
}

final class ReusablePooledService implements ResettableInterface, DisposableInterface
{
    public int $id;

    public function __construct()
    {
        $this->id = ++PooledLifetimeSequence::$created;
    }

    public function reset() : void
    {
        PooledLifetimeSequence::$reset++;
    }

    public function dispose() : void
    {
        PooledLifetimeSequence::$disposed++;
    }
}

final class UnsafePooledService
{
    public int $id;

    public function __construct()
    {
        $this->id = ++PooledLifetimeSequence::$created;
    }
}

$container = makeTestContainer(CreateContainerConfig::create(
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_CI
));

$container->bind(ReusablePooledService::class, ReusablePooledService::class)
    ->pooled(maxSize: 1)
    ->dispose();

$container->bind(UnsafePooledService::class, UnsafePooledService::class)
    ->pooled(maxSize: 1);

$issues = $container->validate([ReusablePooledService::class, UnsafePooledService::class]);
$issueText = implode("\n", $issues);

assertTrue(
    str_contains($issueText, 'does not implement ResettableInterface'),
    'Validation should flag unsafe pooled candidates that cannot reset before reuse.'
);

$container->openScope();
$first = $container->get(ReusablePooledService::class);
$sameScope = $container->get(ReusablePooledService::class);
assertSame($first, $sameScope, 'Pooled services should reuse the checked-out instance inside one scope.');
$container->closeScope();

$container->openScope();
$reused = $container->get(ReusablePooledService::class);
assertSame($first, $reused, 'Pooled services should reuse the available pooled instance across scopes.');
$container->closeScope();

$container->openScope(scopeId: 'outer');
$outer = $container->get(ReusablePooledService::class);
$container->openScope(scopeId: 'inner');
$inner = $container->get(ReusablePooledService::class);
assertNotSame($outer, $inner, 'Nested pooled scopes should build a second instance when the first is still checked out.');
$container->closeScope();
$container->closeScope();

assertSame(4, PooledLifetimeSequence::$reset, 'Pooled services should reset before every return attempt, including overflowed returns.');
assertSame(1, $container->runtimeReport()->scopes['pooledStats']['overflows'] ?? 0, 'Pooled overflow should be tracked in runtime diagnostics.');
assertSame(1, PooledLifetimeSequence::$disposed, 'Overflowed pooled instances should be disposed when they cannot re-enter the bucket.');

$container->openScope();
$unsafeFirst = $container->get(UnsafePooledService::class);
$container->closeScope();
$container->openScope();
$unsafeSecond = $container->get(UnsafePooledService::class);
$container->closeScope();

assertNotSame($unsafeFirst, $unsafeSecond, 'Unsafe pooled instances should be dropped instead of being reused.');
assertSame(2, $container->runtimeReport()->scopes['pooledStats']['unsafe'] ?? 0, 'Unsafe pooled reuse attempts should be tracked in runtime diagnostics.');

$debug = $container->describeService(ReusablePooledService::class);
assertSame('pooled', $debug['lifetimePlan']['name'] ?? null, 'Service diagnostics should expose pooled lifetime plans.');
assertSame(1, $debug['lifetimePlan']['poolSize'] ?? null, 'Pooled lifetime plans should expose pool size.');
assertSame(true, $debug['lifetimePlan']['poolResetBeforeReuse'] ?? null, 'Pooled lifetime plans should expose reset-before-reuse posture.');

echo basename(__FILE__) . " ok\n";
