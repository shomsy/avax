<?php

declare(strict_types=1);

namespace Avax\Database\Registry\Exceptions;

use Avax\Database\Exceptions\DatabaseException;
use Override;
use Throwable;

/**
 * Triggered during failures in the module registration or lifecycle sequence.
 *
 * -- intent: provide specific context for broken feature module initialization.
 */
final class ModuleException extends DatabaseException
{
    /**
     * Constructor capturing the module class and lifecycle stage.
     *
     * -- intent: pinpoint exactly which module and phase failed during boot.
     *
     * @param string         $moduleClass Fully qualified class name of the module
     * @param string         $phase       Registration phase (register/boot/shutdown)
     * @param string         $message     Detailed failure description
     * @param Throwable|null $previous    Underlying trigger
     */
    #[Override]
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
     * Retrieve the failing module's class name.
     *
     * -- intent: identify the problematic feature component.
     *
     * @return string
     */
    public function getModuleClass() : string
    {
        return $this->moduleClass;
    }

    /**
     * Retrieve the lifecycle phase where the failure occurred.
     *
     * -- intent: help developers understand if the failure was in configuration or execution.
     *
     * @return string
     */
    public function getPhase() : string
    {
        return $this->phase;
    }
}


