<?php

declare(strict_types=1);

namespace Avax\Database\Exceptions;

use RuntimeException;

/**
 * Base exception for all database component errors.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Architecture.md#databaseexception
 */
abstract class DatabaseException extends RuntimeException implements DatabaseThrowable {}
