<?php

declare(strict_types=1);

namespace Avax\Database\Exceptions;

use Throwable;

/**
 * Marker interface for all database component throwables.
 *
 * @see docs/Concepts/Architecture.md
 */
interface DatabaseThrowable extends Throwable {}
