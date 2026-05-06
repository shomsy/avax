<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;

abstract class BaseValidator implements ValidatorInterface
{
    protected ?ValidatorInterface $next = null;

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

    abstract public function getName(): string;

    /**
     * @param  array<string, mixed>  $context
     */
    public function supports(array $context): bool
    {
        return true;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function passToNext(array $context): ValidationResult
    {
        if ($this->next !== null) {
            return $this->next->validate($context);
        }

        return ValidationResult::pass();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    abstract public function validate(array $context): ValidationResult;

    /**
     * @param  array<string, mixed>  $context
     */
    protected function combineWithNext(array $context, ValidationResult $currentResult): ValidationResult
    {
        if ($this->next === null) {
            return $currentResult;
        }

        $nextResult = $this->next->validate($context);

        return $currentResult->combine($nextResult);
    }
}
