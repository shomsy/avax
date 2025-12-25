<?php

declare(strict_types=1);

namespace Avax\Database\Exceptions;

use RuntimeException;

/**
 * Base exception for all database operations.
 */
abstract class DatabaseException extends RuntimeException implements DatabaseThrowable {}


