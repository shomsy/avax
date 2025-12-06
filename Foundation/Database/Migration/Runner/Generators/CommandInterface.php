<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Generators;

/**
 * Interface CommandInterface
 *
 * Describes a blueprint for CLI commands in the Avax Foundation.
 * Any CLI command in the Avax system should implement this interface to ensure consistency.
 */
interface CommandInterface
{
    /**
     * Executes the command with provided arguments.
     *
     * The method signature enforces strict typing by using `array` for arguments and
     * `void` for the return type, which aligns with the goals of type safety and clarity.
     *
     * @param array $arguments Arguments passed to the command.
     *
     * Important to note:
     * - The method does not return anything (`void`), reflecting that CLI commands typically
     *   produce their outcome directly via output or side effects (like writing to a file).
     * - This interface ensures any implementing class will provide its own specific logic
     *   for executing commands, maintaining a standard method signature for execution.
     */
    public function execute(array $arguments) : void;
}