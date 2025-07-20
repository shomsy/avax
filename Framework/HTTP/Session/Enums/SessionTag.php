<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session\Enums;

/**
 * Enum `SessionTag`
 *
 * Represents different types of tags that categorize session data in the application.
 * By utilizing an enum, this structure ensures explicit type safety and eliminates
 * the risk of hardcoded or invalid session tag values. This adheres to Domain-Driven Design (DDD)
 * principles by enforcing strong domain boundaries and context-specific behavior.
 *
 * Enums provide clarity and predictability, making the codebase more expressive
 * and maintainable, ensuring clean design principles are upheld.
 *
 * @package Gemini\HTTP\Session\Enums
 */
enum SessionTag: string
{
    /**
     * Flash Tag
     *
     * The flash tag is used to categorize session data that is temporary and designed
     * to last only until the next request is completed. Commonly used for passing
     * notifications or alerts across requests after a redirect.
     *
     * Example Use-Cases:
     * - Temporary feedback ("Your profile has been updated.")
     * - Notifications requiring user acknowledgment during the next request.
     *
     * @var string
     *
     * Usage:
     * ```
     * $session->set(SessionTag::Flash, 'Your changes have been saved!');
     * ```
     */
    case Flash = 'flash';

    /**
     * User Tag
     *
     * The user tag categorizes session data related specifically to the logged-in user.
     * This tag can be leveraged to store user-related metadata, preferences, or credentials
     * that are required during the session's lifecycle.
     *
     * Example Use-Cases:
     * - Authentication tokens.
     * - User preferences (e.g., locale, themes).
     *
     * @var string
     *
     * Usage:
     * ```
     * $session->set(SessionTag::User, ['id' => 123, 'name' => 'John Doe']);
     * ```
     */
    case User = 'user';
}