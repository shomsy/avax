<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 2) . '/bootstrap.php';

use Avax\Components\Container\DI\Capabilities\Runtime\Scopes\ResettableInterface;
use Avax\Components\Container\DI\Capabilities\Runtime\ServicePool;

final class ServicePoolSmokeTest implements ResettableInterface
{
    public int $resets = 0;

    public function reset() : void
    {
        $this->resets++;
    }
}

$pool     = new ServicePool();
$instance = new stdClass();

assertSame(expected: false, actual: $pool->has(abstract: 'shared'), message: 'Service pool should start empty.');

$pool->set(abstract: 'shared', instance: $instance);
assertSame(expected: true, actual: $pool->has(abstract: 'shared'), message: 'Service pool should store shared instances.');
assertSame(expected: $instance, actual: $pool->get(abstract: 'shared'), message: 'Service pool should return the stored shared instance.');
assertSame(expected: 1, actual: $pool->count(), message: 'Service pool should expose the number of stored shared instances.');
assertSame(expected: ['shared'], actual: $pool->ids(), message: 'Service pool should expose deterministic stored ids.');

$pool->forget(abstract: 'shared');
assertSame(expected: null, actual: $pool->get(abstract: 'shared'), message: 'Service pool should forget removed instances.');

$pool->set(abstract: 'shared', instance: $instance);
$pool->flush();
assertSame(expected: null, actual: $pool->get(abstract: 'shared'), message: 'Service pool flush should clear all shared instances.');
assertSame(expected: 0, actual: $pool->count(), message: 'Service pool flush should reset the shared instance count.');

$pooled   = new PoolBucketService();
$released = $pool->releasePooled(abstract: 'pooled', instance: $pooled, maxSize: 2, resetBeforeReuse: true);
assertSame(expected: true, actual: $released['returned'], message: 'Pooled services should return to the available bucket when reset succeeds.');
assertSame(expected: 1, actual: $pooled->resets, message: 'Pooled release should reset the instance before reuse.');
assertSame(expected: true, actual: $pool->hasPooled(abstract: 'pooled'), message: 'Service pool should expose available pooled instances.');
assertSame(expected: 1, actual: $pool->pooledCount(abstract: 'pooled'), message: 'Pooled buckets should expose deterministic counts.');

$checkedOut = $pool->checkoutPooled(abstract: 'pooled');
assertSame(expected: true, actual: $checkedOut['hit'], message: 'Pooled checkout should reuse returned pooled instances.');
assertInstanceOf(expectedClass: PoolBucketService::class, value: $checkedOut['instance'], message: 'Pooled checkout should return the stored object instance.');
assertSame(expected: 0, actual: $pool->pooledCount(abstract: 'pooled'), message: 'Checkout should remove the instance from the available bucket.');

echo basename(path: __FILE__) . " ok\n";
