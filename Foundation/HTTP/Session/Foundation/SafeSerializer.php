<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Foundation;

final class SafeSerializer
{
    private array $allowedClasses = [];

    public function __construct(array $allowedClasses = [])
    {
        $this->allowedClasses = $allowedClasses;
    }

    public function serialize(mixed $value) : string
    {
        return serialize($value);
    }

    public function unserialize(string $data) : mixed
    {
        if (empty($this->allowedClasses)) {
            return unserialize($data, ['allowed_classes' => false]);
        }

        return unserialize($data, ['allowed_classes' => $this->allowedClasses]);
    }
}