<?php

declare(strict_types=1);

namespace Avax\Cache\System\Foundation\Serialization;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\SystemClock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use DateInterval;
use Stringable;

final readonly class SerializedCachePayload implements Stringable
{
    public function __construct(
        public string    $data,
        public string    $format,
        public Timestamp $createdAt,
        public ?string   $checksum = null
    ) {}

    public static function create(string $data, string $format, ?Clock $clock = null) : self
    {
        $clock    ??= new SystemClock();
        $checksum = hash_hmac(algo: 'sha256', data: $data, key: self::class);

        return new self(
            data     : $data,
            format   : $format,
            createdAt: $clock->now(),
            checksum : $checksum
        );
    }

    public function verify() : bool
    {
        if ($this->checksum === null) {
            return true;
        }

        return hash_equals($this->checksum, hash_hmac(algo: 'sha256', data: $this->data, key: self::class));
    }

    public function isOlderThan(DateInterval $interval) : bool
    {
        $now    = Timestamp::now();
        $maxAge = $this->createdAt->add(Duration::fromDateInterval($interval));

        return $now->isAfter($maxAge);
    }

    public function __toString() : string
    {
        return $this->data;
    }
}