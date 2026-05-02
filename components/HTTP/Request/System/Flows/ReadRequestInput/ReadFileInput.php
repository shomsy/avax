<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Flows\ReadRequestInput;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;

final class ReadFileInput
{
    public function read(RequestInterface $request, string $key) : mixed
    {
        return $request->getUploadedFiles()[$key] ?? null;
    }
}
