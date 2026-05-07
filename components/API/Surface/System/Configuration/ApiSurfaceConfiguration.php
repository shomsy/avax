<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Configuration;

interface ApiSurfaceConfiguration
{
    public function getVersion() : string;

    public function getTitle() : string;

    public function getSummary() : string;

    public function isStrictMode() : bool;

    public function getBasePath() : string;
}
