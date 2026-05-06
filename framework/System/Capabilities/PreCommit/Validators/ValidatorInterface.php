<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;

/**
 * Validator Interface
 *
 * Part of Chain of Responsibility pattern.
 * Each validator checks specific rules and can pass to next validator.
 */
interface ValidatorInterface
{
    public function setNext(ValidatorInterface $validator): ValidatorInterface;

    /**
     * @param  array<string, mixed>  $context
     */
    public function validate(array $context): ValidationResult;

    public function getName(): string;

    /**
     * @param  array<string, mixed>  $context
     */
    public function supports(array $context): bool;
}
