<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core\Enums;

/**
 * SessionScope Enum
 *
 * Defines the scope/context of a session.
 *
 * @package Avax\HTTP\Session\Core\Enums
 */
enum SessionScope: string
{
    case USER = 'user';
    case SYSTEM = 'system';
    case API = 'api';
}
