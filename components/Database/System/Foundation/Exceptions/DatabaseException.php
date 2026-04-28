<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Foundation\Exceptions;

use RuntimeException;

/**
 * Base exception for all database component errors.
 *
 * @see /docs/Foundation/Database/Concepts/Architecture.md#databaseexception
 */
abstract class DatabaseException extends RuntimeException implements DatabaseThrowable {}
