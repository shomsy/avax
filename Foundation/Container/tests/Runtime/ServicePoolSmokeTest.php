<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Avax\Container\DependencyInjection\Scopes\ResettableInterface;
use Avax\Container\Runtime\ServicePool;

final class PoolBucketService implements ResettableInterface
{
    public int $resets = 0;

    public function reset() : void
    {
        $this->resets++;
    }
}

$pool = new ServicePool();
$instance = new stdClass();

assertSame(false, $pool->has('shared'), 'Service pool should start empty.');

$pool->set('shared', $instance);
assertSame(true, $pool->has('shared'), 'Service pool should store shared instances.');
assertSame($instance, $pool->get('shared'), 'Service pool should return the stored shared instance.');
assertSame(1, $pool->count(), 'Service pool should expose the number of stored shared instances.');
assertSame(['shared'], $pool->ids(), 'Service pool should expose deterministic stored ids.');

$pool->forget('shared');
assertSame(null, $pool->get('shared'), 'Service pool should forget removed instances.');

$pool->set('shared', $instance);
$pool->flush();
assertSame(null, $pool->get('shared'), 'Service pool flush should clear all shared instances.');
assertSame(0, $pool->count(), 'Service pool flush should reset the shared instance count.');

$pooled = new PoolBucketService();
$released = $pool->releasePooled('pooled', $pooled, maxSize: 2, resetBeforeReuse: true);
assertSame(true, $released['returned'], 'Pooled services should return to the available bucket when reset succeeds.');
assertSame(1, $pooled->resets, 'Pooled release should reset the instance before reuse.');
assertSame(true, $pool->hasPooled('pooled'), 'Service pool should expose available pooled instances.');
assertSame(1, $pool->pooledCount('pooled'), 'Pooled buckets should expose deterministic counts.');

$checkedOut = $pool->checkoutPooled('pooled');
assertSame(true, $checkedOut['hit'], 'Pooled checkout should reuse returned pooled instances.');
assertInstanceOf(PoolBucketService::class, $checkedOut['instance'], 'Pooled checkout should return the stored object instance.');
assertSame(0, $pool->pooledCount('pooled'), 'Checkout should remove the instance from the available bucket.');

echo basename(__FILE__) . " ok\n";
