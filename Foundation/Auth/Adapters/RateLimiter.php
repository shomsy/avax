    private int $defaultMaxAttempts = 5;

    /**
     * The default duration (in seconds) for which a lockout is enforced.
     */
    private int $defaultLockoutDuration = 300; // 5 minutes lockout duration

    /**
     * Constructor with session dependency injection.
     *
     * @param SessionInterface $session The session interface for managing session data.
     */
    public function __construct(private readonly SessionInterface $session) {}

    /**
     * Determines if a user or identifier can attempt an action based on rate limits.
     *
     * @param string   $identifier  Unique identifier for the user or IP.
     * @param int|null $maxAttempts Optional maximum attempts.
     * @param int|null $timeWindow  Optional time window for lockout (seconds).
     *
     * @return bool True if attempt is allowed, false if locked out.
     */
    public function canAttempt(string $identifier, int|null $maxAttempts = null, int|null $timeWindow = null) : bool
    {
        $maxAttempts ??= $this->defaultMaxAttempts;

        $attempts = $this->getAttempts(identifier: $identifier);

        return $attempts < $maxAttempts || ! $this->isLockedOut(identifier: $identifier);
    }

    /**
     * Retrieves the number of attempts made for a specific identifier.
     *
     * @param string $identifier The user's unique identifier (e.g., email or IP).
     *
     * @return int The number of attempts.
     */
    private function getAttempts(string $identifier) : int
    {
        $attemptsKey = $this->getSessionKey(identifier: $identifier, property: 'attempts');

        return $this->session->get(key: $attemptsKey, default: 0);
    }

    /**
     * Generates a session key for the given identifier and property.
     *
     * @param string $identifier The user's unique identifier (e.g., email or IP).
     * @param string $property   The property (e.g., 'attempts' or 'lockout_until').
     *
     * @return string The generated session key.
     */
    private function getSessionKey(string $identifier, string $property) : string
    {
        return hash('sha256', sprintf('rate_limiter_%s_%s', $identifier, $property));
    }

    /**
     * Checks if a user or identifier is locked out.
     *
     * @param string $identifier The user's unique identifier (e.g., email or IP).
     *
     * @return bool True if the user is locked out, false otherwise.
     */
    private function isLockedOut(string $identifier) : bool
    {
        $lockoutKey   = $this->getSessionKey(identifier: $identifier, property: 'lockout_until');
        $lockoutUntil = $this->session->get(key: $lockoutKey);

        return $lockoutUntil && CarbonImmutable::now() < CarbonImmutable::parse(time: $lockoutUntil);
    }

    /**
     * Records a failed attempt and enforces lockout if the maximum is reached.
     *
     * @param string   $identifier  Unique identifier for the user or IP.
     * @param int|null $maxAttempts Optional maximum attempts.
     * @param int|null $timeWindow  Optional lockout duration (seconds).
     */
    public function recordFailedAttempt(
        string   $identifier,
        int|null $maxAttempts = null,
        int|null $timeWindow = null
    ) : void {
        $maxAttempts ??= $this->defaultMaxAttempts;
        $timeWindow  ??= $this->defaultLockoutDuration;

        $attemptsKey = $this->getSessionKey(identifier: $identifier, property: 'attempts');
        $attempts    = $this->getAttempts(identifier: $identifier) + 1;

        $this->session->set(key: $attemptsKey, value: $attempts);

        if ($attempts >= $maxAttempts) {
            $this->lockOut(identifier: $identifier, duration: $timeWindow);
        }
    }

    /**
     * Sets a lockout period for the user after exceeding the maximum attempts.
     *
     * @param string $identifier Unique identifier for the user or IP.
     * @param int    $duration   Lockout duration in seconds.
     */
    private function lockOut(string $identifier, int $duration) : void
    {
        $lockoutKey   = $this->getSessionKey(identifier: $identifier, property: 'lockout_until');
        $lockoutUntil = CarbonImmutable::now()->addSeconds($duration)->toDateTimeString();

        $this->session->set(key: $lockoutKey, value: $lockoutUntil);
    }

    /**
     * Resets the attempt counter and lockout state for a specific identifier.
     *
     * @param string $identifier The user's unique identifier (e.g., email or IP).
     */
    public function resetAttempts(string $identifier) : void
    {
        $attemptsKey = $this->getSessionKey(identifier: $identifier, property: 'attempts');
        $lockoutKey  = $this->getSessionKey(identifier: $identifier, property: 'lockout_until');

        $this->session->delete(key: $attemptsKey);
        $this->session->delete(key: $lockoutKey);
    }
}
