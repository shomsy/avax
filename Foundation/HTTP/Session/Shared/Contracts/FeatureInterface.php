<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Contracts;

interface FeatureInterface
{
    public function boot() : void;

    public function terminate() : void;

    public function getName() : string;
}
