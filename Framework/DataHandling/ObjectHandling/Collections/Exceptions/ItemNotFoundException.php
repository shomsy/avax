<?php

declare(strict_types=1);

namespace Gemini\DataHandling\ObjectHandling\Collections\Exceptions;

use Exception;

/**
 * Exception thrown when a specified item is not found in the collection.
 *
 * This exception is used to signal cases where an operation expected a specific
 * item to be available in the collection, but it wasn't found. It simplifies
 * error handling across the application by providing a specific exception type
 * for missing items, allowing for cleaner and more specific catch blocks.
 */
class ItemNotFoundException extends Exception
{
    /**
     * The default exception message indicating the item was not found.
     *
     * This message is pre-set to provide a consistent error message throughout
     * the application whenever an item is missing from a collection, avoiding
     * the need to define a message each time this exception is thrown.
     *
     * @var string
     */
    protected $message = 'The specified item was not found in the collection.';
}
