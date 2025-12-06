<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Support\Bags;

use Avax\HTTP\Session\Contracts\FlashBagInterface;
use InvalidArgumentException;

/**
 * **InputBag**
 *
 * Responsible for flashing and retrieving old input values from the session.
 * This class is primarily designed to aid in repopulating form inputs after validation failures.
 */
final class InputBag extends AbstractFlashBag
{
    /**
     * Session flash key used to store old input data.
     * This constant helps identify the location of input data in the session.
     *
     * @var string
     */
    private const string FLASH_KEY = '_old_input';

    /**
     * Constructor.
     *
     * @param FlashBagInterface $flash FlashBag instance responsible for managing session flash data.
     */
    public function __construct(FlashBagInterface $flash)
    {
        parent::__construct(flash: $flash);
    }

    /**
     * **Flash Input Data**
     *
     * Stores input data in the session for one-time use in the next request.
     * Throws an exception if the provided input data is empty.
     *
     * @param array<string, mixed> $input The associative array of input data to be stored.
     *
     * @return void
     *
     * @throws InvalidArgumentException If $input is an empty array.
     */
    public function flashInput(array $input) : void
    {
        if (empty($input)) {
            throw new InvalidArgumentException(message: 'Input data cannot be empty when flashing to the session.');
        }

        $this->flash->put(key: $this->flashKey(), value: $input);
    }

    /**
     * Retrieves the session flash key used to store old input data.
     *
     * @return string The session flash key as a string.
     */
    protected function flashKey() : string
    {
        return self::FLASH_KEY;
    }

    /**
     * Retrieves all input keys currently stored in the session.
     *
     * @return array<string> A list of flashed input keys.
     */
    public function keys() : array
    {
        return array_keys($this->all());
    }

    /**
     * **Retrieve Flashed Input Data**
     *
     * Fetches flashed input data from the session and ensures it is returned as an array.
     *
     * @return array<string, mixed>|null Returns the flashed data as an array if available,
     *                                   or null if no data exists or the session doesn't hold an array.
     */
    private function getFlashedData() : array|null
    {
        $data = $this->flash->get(key: $this->flashKey(), default: []);

        return is_array($data) ? $data : null;
    }
}
