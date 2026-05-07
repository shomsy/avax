<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Flows\Logout;

use Avax\Components\Identity\Auth\System\System\Foundation\Failure\AuthFailure;

/**
 * LogoutFailed - Exception thrown when logout process fails.
 * 1:1 alignment with refactor.md.
 */
final class LogoutFailed extends AuthFailure {}
