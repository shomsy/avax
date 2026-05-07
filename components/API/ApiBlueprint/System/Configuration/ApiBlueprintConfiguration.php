<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Configuration;

interface ApiBlueprintConfiguration
{
    public function getVersion() : string;

    public function getTitle() : string;

    public function getSummary() : string;

    public function isStrictMode() : bool;

    public function getBasePath() : string;
}
