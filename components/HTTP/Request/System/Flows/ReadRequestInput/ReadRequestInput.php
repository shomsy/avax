<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Flows\ReadRequestInput;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;

final class ReadRequestInput
{
    public function __construct(
        private ReadRouteInput $routeReader,
        private ReadQueryInput $queryReader,
        private ReadBodyInput $bodyReader,
        private ReadFileInput $fileReader
    ) {}

    public function read(RequestInterface $request, string $key, mixed $default = null): mixed
    {
        return $this->routeReader->read($request, $key)
            ?? $this->queryReader->read($request, $key)
            ?? $this->bodyReader->read($request, $key)
            ?? $this->fileReader->read($request, $key)
            ?? $default;
    }
}
