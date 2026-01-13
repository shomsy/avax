<?php

declare(strict_types=1);

namespace Avax\Database\Exceptions;

use Throwable;

/**
 * Marker interface for all database component throwables.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Architecture.md#databasethrowable
 */
interface DatabaseThrowable extends Throwable {}
