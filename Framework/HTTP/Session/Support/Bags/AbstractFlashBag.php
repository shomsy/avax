<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session\Support\Bags;

use Gemini\HTTP\Session\Contracts\FlashBagInterface;
use Gemini\HTTP\Session\Contracts\SessionBagInterface;

/**
 * AbstractFlashBag
 *
 * A base class for implementing session flash bags. Flash bags are used to manage
 * temporary session data that is only available for the next request cycle.
 * This abstraction ensures that common tasks, such as adding, retrieving, and clearing
 * flash data, are handled in a consistent and reusable way.
 *
 * @package Gemini\HTTP\Session\Support\Bags
 */
abstract class AbstractFlashBag implements SessionBagInterface
{
    /**
     * Constructor to initialize the FlashBagInterface dependency.
     *
     * @param FlashBagInterface $flash An implementation of FlashBagInterface used to manage flash session data.
     */
    public function __construct(protected FlashBagInterface $flash) {}

    /**
     * Retrieve and remove a value from the flash bag in one call.
     * This operation is destructive – once the value is read, it is deleted from storage.
     *
     * @param string     $key     The key to pull from the flash bag.
     * @param mixed|null $default Optional fallback value if the key does not exist.
     *
     * @return mixed|null The pulled value or the default if not found.
     */
    public function pull(string $key, mixed $default = null) : mixed
    {
        // Retrieve the value, then remove it from storage before returning.
        $value = $this->get(key: $key, default: $default);
        $this->forget(key: $key);

        return $value;
    }

    /**
     * Retrieve a value from the flash bag, or return a default value if the key does not exist.
     *
     * @param string     $key     The key to retrieve from the flash bag.
     * @param mixed|null $default The default value to return if the key does not exist (optional).
     *
     * @return mixed|null The value associated with the key, or the default value.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        // Retrieve all flash data and attempt to return the value for the given key.
        return $this->all()[$key] ?? $default;
    }

    /**
     * Retrieve all key-value pairs currently stored in the flash bag.
     *
     * @return array<string, mixed> An associative array of all flash data.
     */
    public function all() : array
    {
        // Fetch all flash data using the unique flash key and ensure it is an array.
        $data = $this->flash->get(key: $this->flashKey(), default: []);

        // Verify the data is a valid array before returning it; otherwise, return an empty array.
        return is_array($data) ? $data : [];
    }

    /**
     * Retrieve the unique flash key associated with this specific bag.
     *
     * This function must be implemented by subclasses to specify a unique identifier
     * for their flash storage within the session.
     *
     * @return string The flash key associated with the bag.
     */
    abstract protected function flashKey() : string;

    /**
     * Remove a specific key-value pair from the flash bag.
     *
     * @param string $key The key to remove from the flash bag.
     *
     * @return void
     */
    public function forget(string $key) : void
    {
        // Retrieve all existing flash data.
        $data = $this->all();

        // Check if the key exists and remove it if present.
        if (array_key_exists($key, $data)) {
            unset($data[$key]);

            // Save the updated data back to the flash storage.
            $this->flash->put(
                key  : $this->flashKey(),
                value: $data
            );
        }
    }

    /**
     * Store a new key-value pair in the flash bag.
     *
     * If the key already exists, its value will be overwritten with the new value.
     *
     * @param string $key   The key under which the value will be stored.
     * @param mixed  $value The value to associate with the given key.
     *
     * @return void
     */
    public function put(string $key, mixed $value) : void
    {
        // Retrieve all existing flash data.
        $data = $this->all();

        // Associate the new value with the specified key.
        $data[$key] = $value;

        // Save the updated data back to the flash storage.
        $this->flash->put(
            key  : $this->flashKey(),
            value: $data
        );
    }

    /**
     * Determine whether the flash bag contains a specific key.
     *
     * @param string $key The key to check for existence.
     *
     * @return bool True if the key exists; false otherwise.
     */
    public function has(string $key) : bool
    {
        // Use array_key_exists to verify if the key exists within the retrieved flash data.
        return array_key_exists($key, $this->all());
    }

    /**
     * Clear all data stored in the flash bag.
     *
     * This operation will remove all stored key-value pairs and reset the storage.
     *
     * @return void
     */
    public function clear() : void
    {
        // Update the flash storage by setting an empty array to the flash key.
        $this->flash->put(
            key  : $this->flashKey(),
            value: []
        );
    }
}
