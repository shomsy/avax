<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface\Http;

use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;

interface HttpKernelInterface
{
    public function handle(RuntimeRequest $request) : RuntimeResponse;
}
