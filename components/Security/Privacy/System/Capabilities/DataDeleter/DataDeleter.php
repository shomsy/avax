<?php

declare(strict_types=1);

namespace Avax\Components\Security\Privacy\System\Capabilities\DataDeleter;

final class DataDeleter
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function hardDelete(array &$data, string $userId) : array
    {
        return $this->removeUserData(data: $data, userId: $userId);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function removeUserData(array $data, string $userId) : array
    {
        return array_filter(
            array   : $data,
            callback: static fn (array $record) : bool => ($record['user_id'] ?? '') !== $userId
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function anonymize(array &$data, string $userId) : array
    {
        $anonymized = $this->removeUserData(data: $data, userId: $userId);

        return $anonymized;
    }
}
