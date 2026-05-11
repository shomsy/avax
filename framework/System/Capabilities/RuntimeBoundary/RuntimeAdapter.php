<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeBoundary;

interface RuntimeAdapter
{
    public function name() : string;

    public function isAvailable() : bool;

    /** @return array<string, bool> */
    public function capabilities() : array;
}
