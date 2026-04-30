<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Flows\ReadRequestInput;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;

final class ReadBodyInput
{
    public function read(RequestInterface $request, string $key): mixed
    {
        $body = $request->getParsedBody();
        if (is_array($body)) {
            return $body[$key] ?? null;
        }

        return null;
    }
}
