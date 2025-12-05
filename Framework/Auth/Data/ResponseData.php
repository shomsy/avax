    /**
     * AccessControl ID, must be an integer.
     */
    public int $id;

    /**
     * AccessControl email, must be a valid string format.
     */
    public string $email;

    /**
     * AccessControl username, a string identifier for the user.
     */
    public string $username;

    /**
     * AccessControl roles, an array to hold roles or null if no roles are assigned.
     * Nullable type written in longer format for clarity.
     */
    public array|null $roles = null;
}
