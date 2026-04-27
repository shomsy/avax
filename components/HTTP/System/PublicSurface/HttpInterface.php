<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\PublicSurface;

interface HttpInterface
{
    public function request(): Request;

    public function response(): Response;
}