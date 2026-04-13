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
$id     = $generator->next(prefix: 'svc_');

assertTrue(condition: $second >= $first, message: 'Clock should move forward.');
assertTrue(condition: str_starts_with($id, 'svc_'), message: 'Generated ids should preserve the prefix.');
assertTrue(condition: strlen($id) > 4, message: 'Generated ids should include random content.');

echo basename(__FILE__) . " ok\n";
