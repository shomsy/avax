<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Register;

/**
 * ValidateRegistrationData - Action to validate registration inputs.
 * 1:1 alignment with refactor.md.
 */
final readonly class ValidateRegistrationData
{
    /**
     * @throws RegistrationFailed
     */
    public function execute(RegistrationData $registrationData) : void
    {
        if ($registrationData->email === '' || $registrationData->email === '0' || ($registrationData->password === '' || $registrationData->password === '0')) {
            throw new RegistrationFailed('Email and password are required');
        }

        if (strlen($registrationData->password) < 8) {
            throw new RegistrationFailed('Password must be at least 8 characters');
        }
    }
}
