<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;

/**
 * Abstract Base Validator
 * 
 * Provides common functionality for all validators.
 */
abstract class BaseValidator implements ValidatorInterface
{
    protected ?ValidatorInterface $next = null;
    protected string $name;

    public function __construct(string $name = '')
    {
        $this->name = $name ?: static::class;
    }

    public function setNext(?ValidatorInterface $validator): ValidatorInterface
    {
        $this->next = $validator;
        return $validator;
    }

    abstract public function validate(array $context): ValidationResult;

    abstract public function getName(): string;

    /**
     * Check if this validator should run for given context
     */
    public function supports(array $context): bool
    {
        return true;
    }

    /**
     * Pass to next validator in chain
     */
    protected function passToNext(array $context): ValidationResult
    {
        if ($this->next !== null && $this->next->supports($context)) {
            return $this->next->validate($context);
        }
        return ValidationResult::pass('End of validation chain');
    }

    /**
     * Combine current result with next validator
     */
    protected function combineWithNext(array $context, ValidationResult $current): ValidationResult
    {
        if ($this->next !== null && $this->next->supports($context)) {
            $nextResult = $this->next->validate($context);
            return $current->combine($nextResult);
        }
        return $current;
    }
}
