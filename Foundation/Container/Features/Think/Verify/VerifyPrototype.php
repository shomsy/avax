<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Verify;

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;
use RuntimeException;

/**
 * The "Quality Assurance" engine for class blueprints.
 *
 * VerifyPrototype is responsible for performing a
 * "Post-Analysis Health Check" on every {@see ServicePrototype} created.
 * While the Analyzer (Think/Analyze) is good at reading code, it doesn't
 * judge it. This service takes those raw readings and ensures they meet
 * strict architectural requirements—for example, ensuring that every
 * injection point actually has a valid type hint.
 *
 * @see     docs/Features/Think/Verify/VerifyPrototype.md
 * @see     ServicePrototype The object being validated.
 */
final readonly class VerifyPrototype
{
    /**
     * Perform a batch validation on a list of blueprints.
     *
     * @param ServicePrototype[] $prototypes The list of blueprints to audit.
     *
     * @return array{valid: ServicePrototype[], invalid: array<string, string>, summary: array{total: int, valid: int,
     *                      invalid: int}}
     *
     * @see docs/Features/Think/Verify/VerifyPrototype.md#method-validatebatch
     */
    public function validateBatch(array $prototypes) : array
    {
        $valid   = [];
        $invalid = [];

        foreach ($prototypes as $prototype) {
            try {
                $this->validate(prototype: $prototype);
                $valid[] = $prototype;
            } catch (RuntimeException $e) {
                $invalid[$prototype->class] = $e->getMessage();
            }
        }

        return [
            'valid'   => $valid,
            'invalid' => $invalid,
            'summary' => [
                'total'   => count($prototypes),
                'valid'   => count($valid),
                'invalid' => count($invalid),
            ],
        ];
    }

    /**
     * Enforce strict rules on a single blueprint.
     *
     * This method will throw a RuntimeException if any of the following are true:
     * 1. The class is marked as non-instantiable (e.g. abstract).
     * 2. A constructor parameter lacks a resolvable type.
     * 3. An injected property lacks a resolvable type.
     * 4. An injected method parameter lacks a resolvable type.
     *
     * @param ServicePrototype $prototype The blueprint to audit.
     *
     * @throws RuntimeException If any rule is violated.
     *
     * @see docs/Features/Think/Verify/VerifyPrototype.md#method-validate
     */
    public function validate(ServicePrototype $prototype) : void
    {
        if (! $prototype->isInstantiable) {
            throw new RuntimeException(message: "Cannot validate non-instantiable prototype for class: {$prototype->class}");
        }

        if ($prototype->constructor) {
            $this->validateMethodPrototype(method: $prototype->constructor, context: 'constructor', class: $prototype->class);
        }

        foreach ($prototype->injectedProperties as $property) {
            if (! $property->type) {
                throw new RuntimeException(message: "Property [{$property->name}] in class [{$prototype->class}] has no resolvable type for injection");
            }
        }

        foreach ($prototype->injectedMethods as $method) {
            $this->validateMethodPrototype(method: $method, context: "method {$method->name}", class: $prototype->class);
        }
    }

    /**
     * Recursive validator for method signatures.
     */
    private function validateMethodPrototype(MethodPrototype $method, string $context, string $class) : void
    {
        foreach ($method->parameters as $parameter) {
            if (! $parameter->type) {
                throw new RuntimeException(message: "Parameter [{$parameter->name}] in {$context} of class [{$class}] has no resolvable type");
            }
        }
    }
}
