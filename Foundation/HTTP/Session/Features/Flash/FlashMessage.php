<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Flash;

/**
 * FlashMessage Value Object
 *
 * Immutable representation of a flash message.
 *
 * Flash messages are temporary notifications that persist for exactly one request
 * cycle, following the Post-Redirect-Get (PRG) pattern.
 *
 * Enterprise Rules:
 * - Immutability: Once created, cannot be modified.
 * - Type Safety: Enforces message type validation.
 * - Serializable: Can be stored in session storage.
 *
 * Usage:
 *   $flash = new FlashMessage(
 *       key: 'success',
 *       value: 'Profile updated successfully!',
 *       type: 'success'
 *   );
 *
 * @package Avax\HTTP\Session\Features\Flash
 */
final readonly class FlashMessage
{
    /**
     * Valid flash message types.
     */
    private const VALID_TYPES = ['success', 'error', 'warning', 'info'];

    /**
     * FlashMessage Constructor.
     *
     * @param string $key   The unique identifier for this flash message.
     * @param mixed  $value The message content (typically string, but can be array/object).
     * @param string $type  The message type (success, error, warning, info).
     */
    public function __construct(
        public string $key,
        public mixed $value,
        public string $type = 'info'
    ) {
        // Guard: Validate message type.
        if (!in_array($this->type, self::VALID_TYPES, strict: true)) {
            throw new \InvalidArgumentException(
                message: "Invalid flash message type: {$this->type}. " .
                    "Valid types: " . implode(', ', self::VALID_TYPES)
            );
        }

        // Guard: Validate key is not empty.
        if (trim($this->key) === '') {
            throw new \InvalidArgumentException(
                message: 'Flash message key cannot be empty'
            );
        }
    }

    /**
     * Check if this is a success message.
     *
     * @return bool True if type is 'success'.
     */
    public function isSuccess(): bool
    {
        return $this->type === 'success';
    }

    /**
     * Check if this is an error message.
     *
     * @return bool True if type is 'error'.
     */
    public function isError(): bool
    {
        return $this->type === 'error';
    }

    /**
     * Check if this is a warning message.
     *
     * @return bool True if type is 'warning'.
     */
    public function isWarning(): bool
    {
        return $this->type === 'warning';
    }

    /**
     * Check if this is an info message.
     *
     * @return bool True if type is 'info'.
     */
    public function isInfo(): bool
    {
        return $this->type === 'info';
    }

    /**
     * Convert to array for serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
        ];
    }

    /**
     * Create from array (deserialization).
     *
     * @param array<string, mixed> $data The array data.
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'] ?? '',
            value: $data['value'] ?? null,
            type: $data['type'] ?? 'info'
        );
    }
}
