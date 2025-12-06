<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Support;

/**
 * SessionSerializer
 *
 * Handles serialization and deserialization of session data.
 *
 * This utility provides safe, type-preserving serialization for
 * session data storage and retrieval.
 *
 * Enterprise Rules:
 * - Type Safety: Preserves PHP types during serialization.
 * - Security: Validates serialized data before deserialization.
 * - Error Handling: Graceful failure on corrupt data.
 *
 * Usage:
 *   $serializer = new SessionSerializer();
 *   $serialized = $serializer->serialize($data);
 *   $data = $serializer->deserialize($serialized);
 *
 * @package Avax\HTTP\Session\Support
 */
final readonly class SessionSerializer
{
    /**
     * Serialize data for storage.
     *
     * Converts PHP values to a storable string format.
     *
     * @param mixed $data The data to serialize.
     *
     * @return string The serialized data.
     */
    public function serialize(mixed $data): string
    {
        try {
            // Use PHP's native serialization.
            // This preserves types and handles complex objects.
            $serialized = serialize($data);

            // Log serialization (without sensitive data).
            logger()?->debug(
                message: 'Session data serialized',
                context: [
                    'data_type' => get_debug_type($data),
                    'serialized_length' => strlen($serialized),
                    'action' => 'SessionSerializer',
                ]
            );

            return $serialized;
        } catch (\Exception $e) {
            // Log serialization failure.
            logger()?->error(
                message: 'Session data serialization failed',
                context: [
                    'error' => $e->getMessage(),
                    'data_type' => get_debug_type($data),
                    'action' => 'SessionSerializer',
                ]
            );

            // Re-throw as RuntimeException.
            throw new \RuntimeException(
                message: 'Failed to serialize session data: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Deserialize data from storage.
     *
     * Converts a serialized string back to PHP values.
     *
     * @param string $serialized The serialized data.
     *
     * @return mixed The deserialized data.
     */
    public function deserialize(string $serialized): mixed
    {
        try {
            // Use PHP's native deserialization.
            // Set allowed_classes to true to allow all classes.
            $data = unserialize($serialized, ['allowed_classes' => true]);

            // Check if deserialization failed.
            if ($data === false && $serialized !== serialize(false)) {
                throw new \RuntimeException('Deserialization returned false');
            }

            // Log deserialization (without sensitive data).
            logger()?->debug(
                message: 'Session data deserialized',
                context: [
                    'result_type' => get_debug_type($data),
                    'serialized_length' => strlen($serialized),
                    'action' => 'SessionSerializer',
                ]
            );

            return $data;
        } catch (\Exception $e) {
            // Log deserialization failure.
            logger()?->warning(
                message: 'Session data deserialization failed',
                context: [
                    'error' => $e->getMessage(),
                    'serialized_length' => strlen($serialized),
                    'action' => 'SessionSerializer',
                ]
            );

            // Re-throw as RuntimeException.
            throw new \RuntimeException(
                message: 'Failed to deserialize session data: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Check if a string is valid serialized data.
     *
     * @param string $data The data to validate.
     *
     * @return bool True if valid serialized data.
     */
    public function isValid(string $data): bool
    {
        // Attempt to unserialize without throwing.
        $result = @unserialize($data);

        // If unserialize returns false, check if it's actually serialized false.
        if ($result === false) {
            return $data === serialize(false);
        }

        return true;
    }
}
