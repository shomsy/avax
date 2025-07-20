<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session\Support\Bags;

use Gemini\HTTP\Session\Contracts\FlashBagInterface;

/**
 * Class ValidationBag
 *
 * Provides functionality to manage flashable validation errors for form input fields.
 * This class enables temporary storage of validation errors in a session flash bag, allowing them
 * to persist across a single request-response cycle.
 *
 * @package Gemini\HTTP\Session\Support\Bags
 */
final class ValidationBag extends AbstractFlashBag
{
    /**
     * The key used to store validation errors in the flash bag.
     *
     * @var string
     */
    private const string FLASH_KEY = '_errors';

    /**
     * Constructor.
     *
     * @param FlashBagInterface $flash FlashBag instance responsible for session flash error storage.
     */
    public function __construct(FlashBagInterface $flash)
    {
        parent::__construct(flash: $flash);
    }

    /**
     * Stores a set of validation errors in the flash bag for temporary usage.
     *
     * @param array<string, string|array<string>> $errors Associative array containing validation errors.
     *
     * @return void
     */
    public function flashErrors(array $errors) : void
    {
        $this->flash->put(key: $this->flashKey(), value: $errors);
    }

    /**
     * Retrieve the unique flash key associated with this specific bag.
     *
     * @return string The flash key associated with the bag.
     */
    protected function flashKey() : string
    {
        return self::FLASH_KEY;
    }

    /**
     * Retrieves the list of all input key names that have validation errors.
     *
     * @return array<string> An array of key names that contain validation errors.
     */
    public function keys() : array
    {
        return array_keys($this->all());
    }
}
