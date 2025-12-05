<?php

declare(strict_types=1);

namespace Gemini\Auth\Adapters;

use Gemini\Auth\Contracts\CredentialsInterface;
use Gemini\Auth\Contracts\RoleInterface;
use Gemini\Auth\Contracts\UserInterface;
use Gemini\Auth\Contracts\UserSourceInterface;
use Gemini\Auth\Data\RegistrationData;
use Gemini\Auth\Adapters\PasswordHasher;
use Gemini\Database\QueryBuilder\QueryBuilder;

readonly class UserDataSource implements UserSourceInterface
{
    public function __construct(
        private QueryBuilder   $queryBuilder,
        private PasswordHasher $passwordHasher
    ) {}

    /**
     * Retrieve a user based on a set of credentials.
     *
     * @throws \Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    public function retrieveByCredentials(CredentialsInterface $credentials) : UserInterface|null
    {
        $identifierKey   = $credentials->getIdentifierKey();
        $identifierValue = $credentials->getIdentifierValue();

        $result = $this->queryBuilder
            ->table(tableName: 'users')
            ->where(column: $identifierKey, value: $identifierValue)
            ->first();

        return $result ? $this->mapToInterface(data: $result) : null;
    }

    /**
     * Maps a database record to an instance of a class implementing UserInterface.
     */
    private function mapToInterface(object|array|null $data) : UserInterface|null
    {
        if (! $data) {
            return null;
        }

        $data = is_array($data) ? (object) $data : $data;

        return new class ($data) implements UserInterface {
            public readonly int    $id;

            public readonly string $email;

            public readonly string $username;

            private string         $password;

            public readonly array  $roles;

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

            public function setPassword(string $password) : void { $this->password = $password; }

            public function hasPermission(string $permission) : bool
            {
                return in_array(
                    $permission,
                    $this->roles,
                    true
                );
            }

            public function hasRole(string $role) : bool { return in_array($role, $this->roles, true); }

            public function addRole(RoleInterface $role) : void { /* TODO: Implement */ }

            public function removeRole(string $role) : void { /* TODO: Implement */ }
        };
    }

    /**
     * Validate the provided credentials against the stored user credentials.
     */
    public function validateCredentials(UserInterface $user, CredentialsInterface $credentials) : bool
    {
        return $this->passwordHasher->verify($credentials->getPassword(), $user->getPassword());
    }

    /**
     * Creates a new user based on the provided registration data.
     *
     * @throws \Exception
     */
    public function createUser(RegistrationData $registrationData) : UserInterface
    {
        $userData = [
            'first_name' => $registrationData->first_name,
            'last_name'  => $registrationData->last_name,
            'username'   => $registrationData->username,
            'email'      => $registrationData->email,
            'password'   => $this->passwordHasher->hash($registrationData->password),
            'is_admin'   => (int) $registrationData->is_admin,
        ];

        logger(message: 'Creating user with data:', context: $userData);

//        $id = $this->queryBuilder
//            ->table(tableName: 'users')
//            ->getLastInsertIdAfterInsert(parameters: $userData);
        $id = '1asaa1'; //TODO: Update this to use the actual ID from the database

        return $this->retrieveById(identifier: $id);
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @throws \Exception
     */
    public function retrieveById(mixed $identifier) : UserInterface|null
    {
        $result = $this->queryBuilder
            ->table(tableName: 'users')
            ->select('id', 'first_name', 'last_name', 'email', 'username', 'password', 'is_admin')
            ->where(column: 'id', value: $identifier)
            ->first();

        return $this->mapToInterface(data: $result);
    }

    /**
     * Update an existing user with the provided data.
     */
    public function updateUser(UserInterface $user, array $data) : bool
    {
        return false;
//        if (isset($data['password'])) {
//            $data['password'] = $this->passwordHasher->hash($data['password']);
//        }
//
//        return $this->queryBuilder
//                   ->table(tableName: 'users')
//                   ->where(column: 'id', operator: '=', value: $user->getId())
//                   ->update(parameters: $data) > 0;
    }

    /**
     * Delete a user by their unique identifier.
     *
     * @throws \Exception
     */
    public function deleteUser(mixed $identifier) : bool
    {
        return false;

//        return $this->queryBuilder
//                   ->table(tableName: 'users')
//                   ->where(column: 'id', operator: '=', value: $identifier)
//                   ->delete() > 0;
    }
}
