<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session\Enums;

/**
 * Enum `SessionBag`
 *
 * Represents the types of session data bags used in the session management system.
 * By using enums, this design allows for strict type safety and eliminates the risk
 * of invalid session bag type usage. Adhering to Clean Code principles, this ensures
 * the operation surrounding session bag types remains self-contained, predictable,
 * and scalable.
 *
 * @package Gemini\HTTP\Session\Enums
 */
enum SessionBag: string
{
    /**
     * Flash Bag
     *
     * Represents the session bag for flash messages — temporary session
     * data that persists only until it is read during the next request.
     *
     * Example usage:
     * - Temporary notifications (e.g., "Your account has been updated.")
     * - Alerts displayed post-redirect.
     *
     * @var string
     */
    case Flash = 'flash';

    /**
     * Input Bag
     *
     * Captures user input data and retains it for redisplaying forms in case
     * of validation failures. This is commonly used to show old input in forms.
     *
     * Example usage:
     * - Preserving user input after form validation errors.
     *
     * @var string
     */
    case Input = 'input';

    /**
     * Validation Bag
     *
     * Holds validation error messages associated with forms or other input validation.
     * This helps maintain a clear separation of data related to failed validations.
     *
     * Example usage:
     * - Displaying form field or general error messages in the UI.
     *
     * @var string
     */
    case Validation = 'validation';
}