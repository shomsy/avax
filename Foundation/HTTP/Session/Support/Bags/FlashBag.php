<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Support\Bags;

use Avax\HTTP\Session\Contracts\FlashBagInterface;
use Avax\HTTP\Session\Contracts\SessionInterface;
use Avax\HTTP\Session\Exceptions\FlashBagKeyNotFoundException;
use InvalidArgumentException;

/**
 * Class FlashBag
 *
 * A specialized, production-grade component for managing temporary flash data in a PHP-based session.
 *
 * Flash data refers to data that persists for the duration of one request/response cycle
 * and is automatically removed afterward. This class provides methods to handle such data
 * with support for re-flashing and deletion.
 *
 * @final
 */
final class FlashBag implements FlashBagInterface
{
    /**
     * @const string FLASH_KEY
     * The session key used to store temporary flash data for the current request cycle.
     */
    private const string FLASH_KEY = '_flash';

    /**
     * @const string FLASH_KEEP_KEY
     * The session key used to track flash data preserved for the next request.
     * When data is "kept," it survives one additional request.
     */
    private const string FLASH_KEEP_KEY = '_flash_keep';

    /**
     * FlashBag constructor.
     *
     * @param SessionInterface $session The session instance responsible for managing flash data.
     */
    public function __construct(private readonly SessionInterface $session) {}

    /**
     * Retrieves and removes a flash value from storage.
     *
     * If the key is not found, a default value is returned or an exception is thrown (if no default is provided).
     *
     * @param string     $key     The key whose value should be retrieved and removed.
     * @param mixed|null $default A fallback value to return if the key is not found.
     *
     * @return mixed The value associated with the specified key.
     * @throws FlashBagKeyNotFoundException If the key does not exist and no default is specified.
     */
    public function pull(string $key, mixed $default = null) : mixed
    {
        // Fetch all flash data currently stored in the session.
        $flashes = $this->getFlashes();

        // Check whether the key exists in the stored flash data.
        if (! array_key_exists($key, $flashes)) {
            // If no default value is provided, throw an exception for missing flash key.
            if ($default === null) {
                throw new FlashBagKeyNotFoundException(message: sprintf('Flash key "%s" not found.', $key));
            }

            return $default;
        }

        // Retrieve the value associated with the key.
        $value = $flashes[$key];

        // Remove the key-value pair from the flash storage.
        unset($flashes[$key]);

        // Save the modified flash data to the session.
        $this->updateFlashes(data: $flashes);

        return $value;
    }

    /**
     * Helper method: Retrieves all flash data from the session.
     *
     * @return array<string, mixed> A collection of key-value pairs in flash storage.
     */
    private function getFlashes() : array
    {
        return $this->session->get(key: self::FLASH_KEY, default: []);
    }

    /**
     * Retrieves a flash value by its key, without removing it from flash storage.
     *
     * If the key is not found, the default value is returned instead.
     *
     * @param string     $key     The key identifying the flash value.
     * @param mixed|null $default A fallback value if the key is not found.
     *
     * @return mixed The value associated with the key or the default value.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        // Fetch all flash data from the session storage.
        $flashes = $this->getFlashes();

        // Return the value from the flash array if it exists, otherwise return the provided default.
        return $flashes[$key] ?? $default;
    }

    /**
     * Helper method: Updates flash data in the session.
     *
     * @param array<string, mixed> $data The updated flash data to store in the session.
     *
     * @return void
     */
    private function updateFlashes(array $data) : void
    {
        $this->session->put(key: self::FLASH_KEY, value: $data);
    }

    /**
     * Stores a value under a specific key in flash storage for one request cycle.
     *
     * @param string $key   A unique identifier for the flash data.
     * @param mixed  $value The value to store in the session.
     *
     * @return void
     * @throws InvalidArgumentException If the provided key is empty.
     */
    public function put(string $key, mixed $value) : void
    {
        // Check that a valid key is provided, preventing empty or invalid keys.
        if (empty($key)) {
            throw new InvalidArgumentException(message: 'Flash key cannot be empty.');
        }

        // Retrieve all stored flash data or initialize an empty array.
        $flashes = $this->getFlashes();

        // Add the given value under the specified key.
        $flashes[$key] = $value;

        // Update the session with the modified flashes.
        $this->updateFlashes(data: $flashes);
    }

    /**
     * Determines whether a flash value is stored under the given key.
     *
     * @param string $key The key to check for existence in flash storage.
     *
     * @return bool True if the key exists, otherwise false.
     */
    public function has(string $key) : bool
    {
        // Checks for key existence in the collection of flash data.
        return array_key_exists($key, $this->getFlashes());
    }

    /**
     * Retrieves all current flash data stored in the session.
     *
     * @return array<string, mixed> An associative array of all key-value pairs in flash storage.
     */
    public function all() : array
    {
        // Return all flash data as an associative array.
        return $this->getFlashes();
    }

    /**
     * Preserves the specified flash key for the subsequent request cycle.
     *
     * Instead of being deleted after the current request, the value is migrated to the "kept" storage.
     *
     * @param string $key The flash key to preserve for the next request.
     *
     * @return void
     */
    public function keep(string $key) : void
    {
        // Retrieve both current flash data and kept flash data.
        $flashes   = $this->getFlashes();
        $flashKeep = $this->getKeptFlashes();

        // Add the specified key's value to the kept flash data if it exists.
        if (array_key_exists($key, $flashes)) {
            $flashKeep[$key] = $flashes[$key];

            // Update the session with the modified kept flash storage.
            $this->updateKeptFlashes(data: $flashKeep);
        }
    }

    /**
     * Helper method: Retrieves all kept flash data from the session.
     *
     * @return array<string, mixed> A collection of key-value pairs explicitly preserved for the next request.
     */
    private function getKeptFlashes() : array
    {
        return $this->session->get(key: self::FLASH_KEEP_KEY, default: []);
    }

    /**
     * Helper method: Updates the kept flash data in the session.
     *
     * @param array<string, mixed> $data The updated kept flash data to store in the session.
     *
     * @return void
     */
    private function updateKeptFlashes(array $data) : void
    {
        $this->session->put(key: self::FLASH_KEEP_KEY, value: $data);
    }

    /**
     * Preserves all flash data, extending its lifespan for the next request.
     *
     * @return void
     */
    public function reflash() : void
    {
        // Retrieve the current flash data from session storage.
        $flashes = $this->getFlashes();

        // Update the kept flashes with all current flashes for re-flashing.
        $this->updateKeptFlashes(data: $flashes);
    }

    /**
     * Removes a specific flash key from storage.
     *
     * @param string $key The key to delete from flash data.
     *
     * @return void
     */
    public function forget(string $key) : void
    {
        // Retrieve all existing flash data from session storage.
        $flashes = $this->getFlashes();

        // Delete the specified key from the flash storage.
        unset($flashes[$key]);

        // Persist the modified flash data to the session.
        $this->updateFlashes(data: $flashes);
    }

    /**
     * Clears all flash values, including both current flashes and kept flashes stored in the session.
     *
     * This method ensures that the flash storage is completely reset, effectively removing
     * both the active flash bag (`_flash`) and the kept/retained flash bag (`_flash_keep`).
     *
     * This can be useful in cases where the flash state needs to be programmatically reset
     * or the session needs to ensure no stale flash values remain.
     *
     * It interacts with two specific session keys:
     * - `self::FLASH_KEY`: Represents the key holding the active flash messages.
     * - `self::FLASH_KEEP_KEY`: Represents the key tracking flash messages marked to persist.
     *
     * @return void
     */
    public function clear() : void
    {
        // Deletes the `_flash` key from the session storage, effectively clearing
        // all active flash messages currently held in the flash bag.
        $this->session->delete(key: self::FLASH_KEY);

        // Deletes the `_flash_keep` key from the session storage, ensuring
        // that no kept flash messages persist into later requests.
        $this->session->delete(key: self::FLASH_KEEP_KEY);
    }

    /**
     * Loads and manages flash data for the current session lifecycle.
     *
     * This method handles the lifecycle of flash messages as follows:
     * - Retrieves the kept flashes from the previous request.
     * - Overwrites the current flash data with the kept flashes.
     * - Clears the kept flashes to reset state for the next request.
     *
     * @return void
     */
    public function load() : void
    {
        // Retrieve flash messages marked to be kept from the previous request.
        $kept = $this->getKeptFlashes();

        // Overwrite the current flash data with the kept flash messages.
        $this->updateFlashes(data: $kept);

        // Reset the kept flash messages for future request cycles.
        $this->updateKeptFlashes(data: []);
    }

    /**
     * Sweeps and clears all flash data at the end of the request lifecycle.
     *
     * This ensures no flash data persists beyond the intended scope.
     *
     * @return void
     */
    public function sweep() : void
    {
        // Clears the existing flash messages stored in the session to avoid persistence.
        $this->updateFlashes(data: []);
    }
}