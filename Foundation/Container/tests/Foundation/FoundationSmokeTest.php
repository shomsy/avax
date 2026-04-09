<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Avax\Container\DI\Foundation\Ids\IdGenerator;
use Avax\Container\DI\Foundation\Time\Clock;

$clock     = new Clock();
$generator = new IdGenerator();

$first = $clock->now();
usleep(10);
$second = $clock->now();
$id     = $generator->next('svc_');

assertTrue($second >= $first, 'Clock should move forward.');
assertTrue(str_starts_with($id, 'svc_'), 'Generated ids should preserve the prefix.');
assertTrue(strlen($id) > 4, 'Generated ids should include random content.');

echo basename(__FILE__) . " ok\n";
