<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\System\Flows\CreateResponse;

use Avax\Components\HTTP\Response\System\System\PublicSurface\Response;
use Avax\Components\HTTP\System\System\Flows\BuildResponse\BuildResponse;

final class CreateResponse
{
    public static function json(mixed $data, int $status = 200) : Response
    {
        return BuildResponse::execute(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($data),
        );
    }

    public static function html(string $html, int $status = 200) : Response
    {
        return BuildResponse::execute(
            $status,
            ['Content-Type' => 'text/html'],
            $html,
        );
    }

    public static function redirect(string $url, int $status = 302) : Response
    {
        return BuildResponse::execute(
            $status,
            ['Location' => $url],
        );
    }

    public static function plain(string $text, int $status = 200) : Response
    {
        return BuildResponse::execute(
            $status,
            ['Content-Type' => 'text/plain'],
            $text,
        );
    }
}
