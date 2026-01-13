<?php

declare(strict_types=1);

namespace Avax\Database\Avax\Connection\Pool\Contracts;

use Avax\Database\Connection\Contracts\DatabaseConnection;

/**
 * The "Shared Library Rules" (Pool Interface).
 *
 * -- what is it?
 * This is an Interface (a contract). It defines the basic operations that
 * ANY connection pool must support.
 *
 * -- how to imagine it:
 * Think of it as the "Signage" on a public library. The sign says:
 * "Anyone who calls themselves a Library must allow people to:
 * 1. Borrow a book (Acquire).
 * 2. Return a book (Release).
 * 3. Tell us the library's name."
 *
 * -- why this exists:
 * So that different parts of our program can use ANY kind of connection
 * pool (like a simple one or a complex enterprise one) without needing
 * to know how it works inside. As long as it follows these three simple
 * rules, it's a valid pool.
 */
interface ConnectionPoolInterface
{
    /**
     * Borrow a database connection from the pool.
     *
     * @return DatabaseConnection A ready-to-use tool to talk to your database.
     */
    public function acquire() : DatabaseConnection;

    /**
     * Hand a database connection back to the pool so others can use it.
     *
     * @param DatabaseConnection $connection The tool you are finished using.
     */
    public function release(DatabaseConnection $connection) : void;

    /**
     * Get the nickname of this pool.
     */
    public function getName() : string;
}
