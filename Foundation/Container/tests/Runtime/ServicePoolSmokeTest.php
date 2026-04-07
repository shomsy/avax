<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Avax\Container\Runtime\ServicePool;

$pool = new ServicePool();
$instance = new stdClass();

assertSame(false, $pool->has('shared'), 'Service pool should start empty.');

$pool->set('shared', $instance);
assertSame(true, $pool->has('shared'), 'Service pool should store shared instances.');
assertSame($instance, $pool->get('shared'), 'Service pool should return the stored shared instance.');

$pool->forget('shared');
assertSame(null, $pool->get('shared'), 'Service pool should forget removed instances.');

$pool->set('shared', $instance);
$pool->flush();
assertSame(null, $pool->get('shared'), 'Service pool flush should clear all shared instances.');

echo basename(__FILE__) . " ok\n";
