<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Register;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;

/**
 * Register - Flow orchestrator for user registration.
 * 1:1 alignment with refactor.md.
 */
final readonly class Register
{
    public function __construct(
        private ValidateRegistrationData $validateRegistrationData,
        private HashRegisteredPassword $hashRegisteredPassword,
        private CreateRegisteredUser $createRegisteredUser,
        private Identity $identity,
    ) {
    }

    /**
     * @throws RegistrationFailed
     */
    public function execute(RegistrationData $registrationData): RegistrationResult
    {
        $this->validateRegistrationData->execute($registrationData);

        $hashedPassword = $this->hashRegisteredPassword->execute($registrationData->password);

        $user = $this->createRegisteredUser->execute($registrationData, $hashedPassword);

        $issuedAuthentication = $this->identity->issue($user);

        return new RegistrationResult($user, $issuedAuthentication);
    }
}
