<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth\ClientRegistration;

use Avax\Auth\System\Capability\OAuth\ClientRegistration\ValidationResult;

/**
 * Composite validator that runs all validation rules for client registration data.
 */
final readonly class CompositeClientDataValidator implements ClientDataValidatorInterface
{
    public function __construct(
        private RedirectUriOwnershipValidator $redirectUriValidator,
        private GrantTypeCompatibilityValidator $grantTypeValidator,
        private PkceRequirementsValidator $pkceValidator,
        private ClientPostureValidator $postureValidator,
        private SenderConstraintPostureValidator $senderConstraintValidator
    ) {}

    /**
     * Validates client registration data using all validators.
     *
     * @param array $clientData Client registration data to validate
     * @return ValidationResult Combined validation result
     */
    public function validate(array $clientData) : ValidationResult
    {
        $allErrors = [];
        
        // Run all validators
        $validators = [
            $this->redirectUriValidator,
            $this->grantTypeValidator,
            $this->pkceValidator,
            $this->postureValidator,
            $this->senderConstraintValidator
        ];
        
        foreach ($validators as $validator) {
            $result = $validator->validate($clientData);
            if (!$result->isValid()) {
                $allErrors = array_merge($allErrors, $result->getErrors());
            }
        }
        
        return new ValidationResult(
            isValid: empty($allErrors),
            errors: $allErrors
        );
    }
}