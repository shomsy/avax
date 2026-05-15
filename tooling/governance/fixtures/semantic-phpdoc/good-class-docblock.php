<?php

/**
 * Good class with semantic PHPDoc for gate fixture testing.
 *
 * This capability owns user registration validation rules so runtime flows
 * can validate registration input without knowing internal constraint rules.
 *
 * It creates validated result objects only. It must not assemble runtime
 * services or act as a public facade.
 */
final readonly class GoodClassDocblock
{
    public function __construct(
        private string $name,
    ) {}

    /**
     * Returns the validated name in normalized form.
     *
     * The method centralizes name normalization so callers do not need to
     * remember trimming or case rules.
     */
    public function getName(): string
    {
        return trim($this->name);
    }
}
