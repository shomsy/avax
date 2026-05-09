<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\SecureRequest\System\PublicSurface;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolation;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolations;
use Avax\Components\DataStack\DataTransfer\System\Flows\CreateDataObject\CreateDataObject;
use Avax\Components\HTTP\SecureRequest\System\Capabilities\SecureRequestValidation\ValidationContext;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestAuthorizationFailed;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestValidationFailed;

/**
 * SecureRequest — HTTP request-as-DTO.
 *
 * Canonical style: public typed properties + PHP attributes.
 * No constructor required. No rules() method.
 *
 * SecureRequest delegates hydration, casting, and attribute validation
 * to DataTransfer. It owns only HTTP input lifecycle, authorization,
 * and request-specific hooks.
 *
 * Lifecycle order:
 * 1. beforeHydration
 * 2. DataTransfer hydrates public typed properties from input
 * 3. afterHydration
 * 4. beforeValidation
 * 5. DataTransfer validates attributes (Required, StringType, Min, Max, etc.)
 * 6. withValidation (custom hook)
 * 7. afterValidation
 * 8. if violations: failedValidation + throw
 * 9. authorize
 * 10. if ! authorize: throw
 * 11. passedValidation
 */
abstract class SecureRequest
{
    /**
     * @var array<string, mixed>
     */
    private array $_input = [];

    /**
     * Get the raw input data.
     *
     * @return array<string, mixed>
     */
    public function getInput() : array
    {
        return $this->_input;
    }

    /**
     * @param array<string, mixed> $input
     *
     * @internal
     */
    public function setInput(array $input) : void
    {
        $this->_input = $input;
    }

    /**
     * Run the full SecureRequest lifecycle.
     *
     * Hydration and attribute validation are delegated to DataTransfer.
     * SecureRequest owns lifecycle hooks, authorization, and custom validation.
     *
     * @param array<string, mixed> $input
     *
     * @internal
     */
    public function runLifecycle(array $input) : void
    {
        $this->beforeHydration();
        $this->setInput($input);

        // Step 2: Delegate hydration + attribute validation to DataTransfer
        $violations = (new CreateDataObject())->hydrateInto(object: $this, input: $input);

        $this->afterHydration();
        $this->beforeValidation();

        // Step 8: If DataTransfer found violations, fail
        if ($violations !== []) {
            $collection = new DataTransferViolations($violations);
            $this->failedValidation($collection);

            throw new SecureRequestValidationFailed(
                message   : 'SecureRequest validation failed.',
                violations: $collection,
            );
        }

        $this->afterValidation();

        // Step 6: Custom validation hook
        $context = new ValidationContext(
            request: $this,
            input  : $input,
        );
        $this->withValidation($context);

        if ($context->hasViolations()) {
            $this->failedValidation($context->violations());

            throw new SecureRequestValidationFailed(
                message   : 'SecureRequest custom validation failed.',
                violations: $context->violations(),
            );
        }

        // Step 9: Authorization
        if (! $this->authorize()) {
            throw new SecureRequestAuthorizationFailed();
        }

        // Step 11: Passed
        $this->passedValidation();
    }

    /**
     * Lifecycle: before hydration begins.
     */
    protected function beforeHydration() : void {}

    /**
     * Lifecycle: after hydration completes.
     */
    protected function afterHydration() : void {}

    /**
     * Lifecycle: before attribute validation.
     */
    protected function beforeValidation() : void {}

    /**
     * Lifecycle: when validation fails.
     */
    protected function failedValidation(DataTransferViolations $violations) : void {}

    /**
     * Lifecycle: after attribute validation succeeds.
     */
    protected function afterValidation() : void {}

    /**
     * Custom validation hook.
     * Override for complex cross-field validation.
     */
    protected function withValidation(ValidationContext $context) : void {}

    /**
     * Determine if the user is authorized.
     * Override in subclasses.
     */
    public function authorize() : bool
    {
        return true;
    }

    /**
     * Lifecycle: after all validation succeeds.
     */
    protected function passedValidation() : void {}
}
