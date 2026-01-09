<?php
declare(strict_types=1);

namespace Avax\HTTP\Context;

/**
 * PHP runtime globals provider.
 *
 * This class is the only place that touches superglobals directly.
 */
final class PhpGlobalsProvider implements GlobalsProviderInterface
{
    public function server() : array
    {
        return is_array($_SERVER ?? null) ? $_SERVER : [];
    }

    public function query() : array
    {
        return is_array($_GET ?? null) ? $_GET : [];
    }

    public function post() : array
    {
        return is_array($_POST ?? null) ? $_POST : [];
    }

    public function cookies() : array
    {
        return is_array($_COOKIE ?? null) ? $_COOKIE : [];
    }

    public function files() : array
    {
        return is_array($_FILES ?? null) ? $_FILES : [];
    }

    public function session() : array
    {
        return is_array($_SESSION ?? null) ? $_SESSION : [];
    }
}
