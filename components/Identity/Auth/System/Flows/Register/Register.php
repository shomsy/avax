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
        private ValidateRegistrationData $validator,
        private HashRegisteredPassword $hasher,
        private CreateRegisteredUser $creator,
        private Identity $identity,
    ) {}

    /**
     * @throws RegistrationFailed
     */
    public function execute(RegistrationData $data): RegistrationResult
    {
        $this->validator->execute($data);

        $hashedPassword = $this->hasher->execute($data->password);

        $user = $this->creator->execute($data, $hashedPassword);

        $issued = $this->identity->issue($user);

        return new RegistrationResult($user, $issued);
    }
}
