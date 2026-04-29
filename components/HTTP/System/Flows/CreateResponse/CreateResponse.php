<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\CreateResponse;

final class CreateResponse
{
    public static function json(mixed $data, int $status = 200): \Avax\Components\HTTP\System\Capabilities\Response
    {
        return \Avax\Components\HTTP\System\Flows\BuildResponse\BuildResponse::execute(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($data),
        );
    }

    public static function html(string $html, int $status = 200): \Avax\Components\HTTP\System\Capabilities\Response
    {
        return \Avax\Components\HTTP\System\Flows\BuildResponse\BuildResponse::execute(
            $status,
            ['Content-Type' => 'text/html'],
            $html,
        );
    }

    public static function redirect(string $url, int $status = 302): \Avax\Components\HTTP\System\Capabilities\Response
    {
        return \Avax\Components\HTTP\System\Flows\BuildResponse\BuildResponse::execute(
            $status,
            ['Location' => $url],
        );
    }

    public static function plain(string $text, int $status = 200): \Avax\Components\HTTP\System\Capabilities\Response
    {
        return \Avax\Components\HTTP\System\Flows\BuildResponse\BuildResponse::execute(
            $status,
            ['Content-Type' => 'text/plain'],
            $text,
        );
    }
}