<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Context\System\Capabilities\Globals;

/**
 * PHP runtime globals provider.
 */
final class PhpGlobalsProvider implements GlobalsProviderInterface
{
    public function server(): array { return $_SERVER ?? []; }
    public function query(): array { return $_GET ?? []; }
    public function post(): array { return $_POST ?? []; }
    public function cookies(): array { return $_COOKIE ?? []; }
    public function files(): array { return $_FILES ?? []; }
    public function session(): array { return $_SESSION ?? []; }
}
