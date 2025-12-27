<?php

declare(strict_types=1);

namespace Avax\Database\Connection\Contracts;

use PDO;

/**
 * The "Rulebook" for any database connection.
 *
 * -- what is it?
 * This is an Interface (a contract). It doesn't perform any real work 
 * itself, but it lists the rules and methods that ANY database connection 
 * must follow to be allowed into our system.
 *
 * -- how to imagine it:
 * Think of it as a "Job Description". It says "If you want to be a 
 * Connection, you must be able to: 
 * 1. Let us use your tools (PDO).
 * 2. Tell us if you're still alive (Ping).
 * 3. Tell us your name."
 *
 * -- why this exists:
 * So that the rest of the application doesn't need to care if it's talking 
 * to a MySQL connection, a Postgres connection, or a "Shared Pool" connection. 
 * As long as they all follow this same rulebook, the application can treat 
 * them exactly the same.
 *
 * -- mental models:
 * - "Contract": A promise that these specific methods will always exist.
 * - "PDO": The actual underlying engine (hammer and nails) used to work 
 *    with bytes and rows.
 */
interface DatabaseConnection
{
    /**
     * Get the actual technical tool (PDO) used to run the queries.
     *
     * @return PDO The active, ready-to-work database engine.
     */
    public function getConnection(): PDO;

    /**
     * Check if the database is still "awake" and responsive.
     *
     * -- how it works:
     * We send a tiny "Heartbeat" signal. If the database replies, we 
     * know the connection is healthy. If not, the line is dead.
     *
     * @return bool True if the database is alive, false otherwise.
     */
    public function ping(): bool;

    /**
     * Get the nickname of this specific connection.
     *
     * -- why this exists:
     * Useful for logging. For example: "Error in connection [primary]" 
     * is much more helpful than just "Error in connection".
     *
     * @return string The technical label (name) of the connection.
     */
    public function getName(): string;
}
