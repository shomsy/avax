<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;

/**
 * Abstract Base Validator
 *
 * Provides common functionality for all validators.
 */
abstract class Validator implements ValidatorInterface
{
    protected ValidatorInterface|null $next = null;

    protected string $name;

    public function __construct(string $name = '')
    {
        $this->name = $name ?: static::class;
    }

    public function setNext(ValidatorInterface $validator): ValidatorInterface
    {
        $this->next = $validator;

        return $validator;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    abstract public function validate(array $context): ValidationResult;

    abstract public function getName(): string;

    /**
     * Check if this validator should run for given context
     */
    /**
     * @param  array<string, mixed>  $context
     */
    public function supports(array $context): bool
    {
        return true;
    }

    /**
     * Pass to next validator in chain
     */
    /**
     * @param  array<string, mixed>  $context
     */
    protected function passToNext(array $context): ValidationResult
    {
        if ($this->next instanceof ValidatorInterface && $this->next->supports($context)) {
            return $this->next->validate($context);
        }

        return ValidationResult::pass('End of validation chain');
    }

    /**
     * Combine current result with next validator
     */
    /**
     * @param  array<string, mixed>  $context
     */
    protected function combineWithNext(array $context, ValidationResult $validationResult): ValidationResult
    {
        if ($this->next instanceof ValidatorInterface && $this->next->supports($context)) {
            $nextResult = $this->next->validate($context);

            return $validationResult->combine($nextResult);
        }

        return $validationResult;
    }
}
