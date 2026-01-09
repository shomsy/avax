<?php

declare(strict_types=1);

namespace Avax\Database\Registry\Exceptions;

use Avax\Database\Exceptions\DatabaseException;
use Throwable;

/**
 * A "Feature Failure" report (Module Error).
 *
 * -- what is it?
 * This is a specialized error that happens when something goes wrong with
 * a specific database "Feature" (Module) while it's starting up or
 * shutting down.
 *
 * -- how to imagine it:
 * Think of an "Accident Report" in a factory. It doesn't just say
 * "Something broke"; it says "The conveyor belt broke while we were
 * trying to turn it on (Boot phase)."
 *
 * -- why this exists:
 * To provide laser-focused diagnostics. It tells you two critical things:
 * 1. Which exact feature failed (e.g., 'QueryBuilder').
 * 2. What it was doing at the time ('Registering', 'Booting', or 'Shutdown').
 *
 * -- mental models:
 * - "Phase": The specific stage of a module's life (Born -> Working -> Dying).
 * - "Immutable": The details of this report cannot be changed once written.
 */
final class ModuleException extends DatabaseException
{
    /**
     * @param string         $moduleClass The name of the feature class that failed.
     * @param string         $phase       The step it was on (e.g., 'booting', 'registering').
     * @param string         $message     A clear explanation of what went wrong.
     * @param Throwable|null $previous    The raw system error that caused the crash.
     */
    public function __construct(
        private readonly string $moduleClass,
        private readonly string $phase,
        string                  $message,
        Throwable|null          $previous = null
    )
    {
        parent::__construct(
            message : "Module [{$moduleClass}] failed during [{$phase}]: {$message}",
            code    : 0,
            previous: $previous
        );
    }

    /**
     * Get the name of the problematic feature.
     */
    public function getModuleClass() : string
    {
        return $this->moduleClass;
    }

    /**
     * Get the stage of life where the error happened.
     */
    public function getPhase() : string
    {
        return $this->phase;
    }
}
