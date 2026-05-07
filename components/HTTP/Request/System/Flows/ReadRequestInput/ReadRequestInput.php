<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Flows\ReadRequestInput;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;

final readonly class ReadRequestInput
{
    public function __construct(
        private ReadRouteInput $readRouteInput,
        private ReadQueryInput $readQueryInput,
        private ReadBodyInput  $readBodyInput,
        private ReadFileInput  $readFileInput,
    ) {}

    public function read(RequestInterface $request, string $key, mixed $default = null) : mixed
    {
        return $this->readRouteInput->read($request, $key)
            ?? $this->readQueryInput->read($request, $key)
            ?? $this->readBodyInput->read($request, $key)
            ?? $this->readFileInput->read($request, $key)
            ?? $default;
    }
}
