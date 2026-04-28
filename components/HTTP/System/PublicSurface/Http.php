<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\PublicSurface;

final class Http implements HttpInterface
{
    public function request(): Request
    {
        return new Request($_SERVER, $_GET, $_POST, $_FILES);
    }

    public function response(): Response
    {
        return new Response();
    }
}