<?php

declare(strict_types=1);

namespace Avax\Auth\Adapters;

use Avax\Auth\Contracts\CredentialsInterface;
use Avax\Auth\Contracts\RoleInterface;
use Avax\Auth\Contracts\UserInterface;
use Avax\Auth\Contracts\UserSourceInterface;
use Avax\Auth\Data\RegistrationDTO;
use Avax\Database\Modules\Query\Builder\QueryBuilder;
use SensitiveParameter;

readonly class UserDataSource implements UserSourceInterface
{
    public function __construct(
        private QueryBuilder                         $queryBuilder,
        #[SensitiveParameter] private PasswordHasher $passwordHasher
    ) {}

    /**
     * Retrieve a user based on a set of credentials.
     *
     * @throws \Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    public function retrieveByCredentials(#[SensitiveParameter] CredentialsInterface $credentials) : UserInterface|null
    {
        $identifierKey   = $credentials->getIdentifierKey();
        $identifierValue = $credentials->getIdentifierValue();

        $result = $this->usersQuery()
            ->where($identifierKey, $identifierValue)
            ->first();

        return $result ? $this->mapToInterface(data: $result) : null;
    }

    private function usersQuery() : QueryBuilder
    {
        return $this->queryBuilder->newQuery()->table('users');
    }

    /**
     * Maps a database record to an instance of a class implementing UserInterface.
     */
    private function mapToInterface(object|array|null $data) : UserInterface|null
    {
        if (! $data) {
            return null;
        }

        $data = is_array(value: $data) ? (object) $data : $data;

        return new class ($data) implements UserInterface {
            public readonly int $id;

            public readonly string $email;

            public readonly string $username;

            private string $password;

            public readonly array $roles;

            public function __construct(object $data)
            {
                $this->id       = $data->id;
                $this->email    = $data->email;
                $this->username = $data->username;
                $this->password = $data->password;
                $this->roles    = $data->roles ?? [];
            }

            public function getId() : int { return $this->id; }

            public function getEmail() : string { return $this->email; }

            public function getUsername() : string { return $this->username; }

            public function getPassword() : string { return $this->password; }

            public function getRoles() : array { return $this->roles; }

            public function setPassword(#[SensitiveParameter] string $password) : void { $this->password = $password; }

            public function hasPermission(string $permission) : bool
            {
                return in_array(
                    needle  : $permission,
                    haystack: $this->roles,
                    strict  : true
                );
            }

            public function hasRole(string $role) : bool { return in_array(needle: $role, haystack: $this->roles, strict: true); }

            public function addRole(RoleInterface $role) : void { /* TODO: Implement */ }

            public function removeRole(string $role) : void { /* TODO: Implement */ }
        };
    }

    /**
     * Validate the provided credentials against the stored user credentials.
     */
    public function validateCredentials(UserInterface $user, #[SensitiveParameter] CredentialsInterface $credentials) : bool
    {
        return $this->passwordHasher->verify(password: $credentials->getPassword(), hashedPassword: $user->getPassword());
    }

    /**
     * Creates a new user based on the provided registration data.
     *
     * @throws \Exception
     */
    public function createUser(RegistrationDTO $RegistrationDTO) : UserInterface
    {
        $userData = [
            'first_name' => $RegistrationDTO->first_name,
            'last_name'  => $RegistrationDTO->last_name,
            'username'   => $RegistrationDTO->username,
            'email'      => $RegistrationDTO->email,
            'password'   => $this->passwordHasher->hash(password: $RegistrationDTO->password),
            'is_admin'   => (int) $RegistrationDTO->is_admin,
        ];

        logger(message: 'Creating user with data:', context: $userData);

        $id = $this->usersQuery()->insertGetId($userData);

        return $this->retrieveById(identifier: $id);
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @throws \Exception
     */
    public function retrieveById(mixed $identifier) : UserInterface|null
    {
        $result = $this->usersQuery()
            ->select('id', 'first_name', 'last_name', 'email', 'username', 'password', 'is_admin')
            ->where('id', $identifier)
            ->first();

        return $this->mapToInterface(data: $result);
    }

    /**
     * Update an existing user with the provided data.
     */
    public function updateUser(UserInterface $user, array $data) : bool
    {
        if (isset($data['password'])) {
            $data['password'] = $this->passwordHasher->hash(password: $data['password']);
        }

        if ($data === []) {
            return false;
        }

        return $this->usersQuery()
            ->where('id', '=', $user->getId())
            ->update($data);
    }

    /**
     * Delete a user by their unique identifier.
     *
     * @throws \Exception
     */
    public function deleteUser(mixed $identifier) : bool
    {
        return $this->usersQuery()
            ->where('id', '=', $identifier)
            ->delete();
    }
}
