<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Flows\ReadRequestInput;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;

final class ReadQueryInput
{
    public function read(RequestInterface $request, string $key): mixed
    {
        return $request->getQueryParams()[$key] ?? null;
    }
}
