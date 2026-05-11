<?php

declare(strict_types=1);

use Avax\Framework\System\PublicSurface\Avax;

require __DIR__.'/../vendor/autoload.php';

$app = Avax::create();

$app->get('/', fn (): string => 'Hello AvaX');

$app->run();
