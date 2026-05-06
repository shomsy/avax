<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\ValidationChain;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;
use Avax\Framework\System\Capabilities\PreCommit\Validators\ValidatorInterface;

/**
 * Chain of Responsibility Orchestrator
 *
 * Manages the validator chain and executes validation pipeline.
 */
class ValidationChain
{
    private ?ValidatorInterface $head = null;

    private ?ValidatorInterface $tail = null;

    /** @var list<ValidatorInterface> */
    private array $validators = [];

    /**
     * Add validator to the chain
     */
    public function add(ValidatorInterface $validator): self
    {
        $this->validators[] = $validator;

        if (! $this->head instanceof ValidatorInterface) {
            $this->head = $validator;
            $this->tail = $validator;
        } else {
            $tail = $this->tail;
            if (! $tail instanceof ValidatorInterface) {
                $this->head = $validator;
                $this->tail = $validator;

                return $this;
            }

            $tail->setNext($validator);
            $this->tail = $validator;
        }

        return $this;
    }

    /**
     * Execute the validation chain
     */
    /**
     * @param  array<string, mixed>  $context
     */
    public function validate(array $context): ValidationResult
    {
        if (! $this->head instanceof ValidatorInterface) {
            return ValidationResult::pass('No validators configured');
        }

        return $this->head->validate($context);
    }

    /**
     * Get all registered validators
     *
     * @return list<ValidatorInterface>
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * Get validator by name
     */
    public function getValidator(string $name): ?ValidatorInterface
    {
        foreach ($this->validators as $validator) {
            if ($validator->getName() === $name) {
                return $validator;
            }
        }

        return null;
    }

    /**
     * Reset the chain
     */
    public function reset(): void
    {
        $this->head = null;
        $this->tail = null;
        $this->validators = [];
    }
}
