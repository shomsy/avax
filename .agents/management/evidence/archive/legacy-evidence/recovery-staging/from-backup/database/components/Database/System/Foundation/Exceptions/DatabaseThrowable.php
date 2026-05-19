<?php

declare(strict_types=1);

namespace Avax\Database\System\Foundation\Exceptions;

use Throwable;

/**
 * Marker interface for all database component throwables.
 *
 * @see /docs/Foundation/Database/Concepts/Architecture.md#databasethrowable
 */
interface DatabaseThrowable extends Throwable
{
}
