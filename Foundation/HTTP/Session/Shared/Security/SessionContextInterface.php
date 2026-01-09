<?php
declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security;

/**
 * Read-only session metadata snapshot.
 */
interface SessionContextInterface
{
    public function sessionId() : string;

    public function userId() : string|int|null;

    public function clientIp() : string|null;

    public function userAgent() : string|null;

    public function isSecure() : bool;
}
