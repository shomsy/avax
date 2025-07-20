<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Commands\Middlewares;

use Gemini\Database\Migration\Runner\Generators\CommandInterface;
use Gemini\DataHandling\ArrayHandling\Arrhae;

/**
 * A stack of middleware functions to be executed as a pipeline.
 *
 * - Extends Arrhae to manage the internal collection of middleware functions.
 * - Allows pushing middleware functions onto the stack.
 * - Executes the middleware functions in sequence, passing control to the `next` middleware.
 */
class MiddlewareStack extends Arrhae
{
    /**
     * Adds a middleware callable to the stack.
     *
     * This method allows chaining by returning the instance.
     *
     * @param callable $middleware The middleware to add.
     *
     * @return self The instance itself for method chaining.
     */
    public function push(callable $middleware) : self
    {
        $this->add($middleware);

        return $this;
    }

    /**
     * Executes the command by passing it through the middleware stack.
     *
     * - The method applies each middleware function to the command.
     * - Ensures that the `next` callable is eventually called.
     *
     * @param CommandInterface $command   The command to execute.
     * @param array            $arguments The arguments for the command.
     * @param callable         $next      The next middleware callable.
     */
    public function execute(
        CommandInterface $command,
        array            $arguments,
        callable         $next
    ) : void {
        // Middleware logic here, last middleware will eventually call $next()
        $next();
    }
}