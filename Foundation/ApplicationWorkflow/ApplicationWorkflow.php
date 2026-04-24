<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow;

use Avax\ApplicationWorkflow\Saga\Saga;

/**
 * ApplicationWorkflow - public owner for workflow capabilities that do not belong inside data storage concerns.
 */
final readonly class ApplicationWorkflow
{
    public function __construct(private Saga $saga) {}

    public static function inMemory() : self
    {
        return new self(saga: Saga::inMemory());
    }

    public function saga() : Saga
    {
        return $this->saga;
    }
}
