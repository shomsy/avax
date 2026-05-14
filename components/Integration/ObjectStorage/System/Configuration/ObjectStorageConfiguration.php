<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Configuration;

interface ObjectStorageConfiguration
{
    public function getBucket() : string;

    public function getRegion() : string;

    public function getEndpoint() : string|null;

    public function getAccessKey() : string|null;

    public function getSecretKey() : string|null;

    public function isPublic() : bool;
}
