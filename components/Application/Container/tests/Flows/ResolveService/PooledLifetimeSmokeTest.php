<?php

declare(strict_types=1);

require_once dirname(2, path: __DIR__) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\DI\Capabilities\Runtime\Scopes\DisposableInterface;
use Avax\Components\Application\Container\DI\Capabilities\Runtime\Scopes\ResettableInterface;

final class PooledLifetimeSequence
{
    public static int $created = 0;

    public static int $reset = 0;

    public static int $disposed = 0;
}

final class ReusablePooledService implements DisposableInterface, ResettableInterface
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

$container = makeTestContainer(config: CreateContainerConfig::create(
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_CI,
));

$container->bind(abstract: ReusablePooledService::class, concrete: ReusablePooledService::class)
    ->pooled(maxSize: 1)
    ->dispose();

$container->bind(abstract: UnsafePooledService::class, concrete: UnsafePooledService::class)
    ->pooled(maxSize: 1);

$issues = $container->validate(serviceIds: [ReusablePooledService::class, UnsafePooledService::class]);
$issueText = implode(separator: "\n", array: $issues);

assertTrue(
    condition: str_contains(haystack: $issueText, needle: 'does not implement ResettableInterface'),
    message  : 'Validation should flag unsafe pooled candidates that cannot reset before reuse.',
);

$container->openScope();
$first = $container->get(id: ReusablePooledService::class);
$sameScope = $container->get(id: ReusablePooledService::class);
assertSame(expected: $first, actual: $sameScope, message: 'Pooled services should reuse the checked-out instance inside one scope.');
$container->closeScope();

$container->openScope();
$reused = $container->get(id: ReusablePooledService::class);
assertSame(expected: $first, actual: $reused, message: 'Pooled services should reuse the available pooled instance across scopes.');
$container->closeScope();

$container->openScope(scopeId: 'outer');
$outer = $container->get(id: ReusablePooledService::class);
$container->openScope(scopeId: 'inner');
$inner = $container->get(id: ReusablePooledService::class);
assertNotSame(expected: $outer, actual: $inner, message: 'Nested pooled scopes should build a second instance when the first is still checked out.');
$container->closeScope();
$container->closeScope();

assertSame(expected: 4, actual: PooledLifetimeSequence::$reset, message: 'Pooled services should reset before every return attempt, including overflowed returns.');
assertSame(expected: 1, actual: $container->runtimeReport()->scopes['pooledStats']['overflows'] ?? 0, message: 'Pooled overflow should be tracked in runtime diagnostics.');
assertSame(expected: 1, actual: PooledLifetimeSequence::$disposed, message: 'Overflowed pooled instances should be disposed when they cannot re-enter the bucket.');

$container->openScope();
$unsafeFirst = $container->get(id: UnsafePooledService::class);
$container->closeScope();
$container->openScope();
$unsafeSecond = $container->get(id: UnsafePooledService::class);
$container->closeScope();

assertNotSame(expected: $unsafeFirst, actual: $unsafeSecond, message: 'Unsafe pooled instances should be dropped instead of being reused.');
assertSame(expected: 2, actual: $container->runtimeReport()->scopes['pooledStats']['unsafe'] ?? 0, message: 'Unsafe pooled reuse attempts should be tracked in runtime diagnostics.');

$debug = $container->describeService(id: ReusablePooledService::class);
assertSame(expected: 'pooled', actual: $debug['lifetimePlan']['name'] ?? null, message: 'Service diagnostics should expose pooled lifetime plans.');
assertSame(expected: 1, actual: $debug['lifetimePlan']['poolSize'] ?? null, message: 'Pooled lifetime plans should expose pool size.');
assertSame(expected: true, actual: $debug['lifetimePlan']['poolResetBeforeReuse'] ?? null, message: 'Pooled lifetime plans should expose reset-before-reuse posture.');

echo basename(path: __FILE__) . " ok\n";
