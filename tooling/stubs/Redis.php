<?php

/**
 * Stub for the Redis PHP extension class.
 */
if (!class_exists('Redis')) {
    class Redis
    {
        public function connect(string $host, int $port = 6379, float $timeout = 0.0): bool { return true; }
        public function auth(mixed $credentials): bool { return true; }
        public function select(int $db): bool { return true; }
        public function set(string $key, mixed $value, mixed $options = null): bool { return true; }
        public function get(string $key): mixed { return false; }
        public function del(string|array $key): int { return 0; }
        public function hMSet(string $key, array $dictionary): bool { return true; }
        public function hGetAll(string $key): array { return []; }
        public function expire(string $key, int $seconds): bool { return true; }
        public function sAdd(string $key, mixed ...$values): int { return 0; }
        public function sRem(string $key, mixed ...$values): int { return 0; }
        public function sMembers(string $key): array { return []; }
        public function exists(string|array $key): int|bool { return false; }
        public function incrBy(string $key, int $value): int { return 0; }
        public function setex(string $key, int $ttl, mixed $value): bool { return true; }
    }
}
