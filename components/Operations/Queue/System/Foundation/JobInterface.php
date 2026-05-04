<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Foundation;

interface JobInterface
{
    public function handle(array $data = []): void;
}
