<?php

declare(strict_types=1);

namespace Avax\DataLayer\ProtectStoredData;

enum MaskingAlgorithm: string
{
    case PARTIAL           = 'partial';
    case FULL              = 'full';
    case HASH              = 'hash';
    case TOKENIZE          = 'tokenize';
    case FORMAT_PRESERVING = 'format_preserving';
}

final readonly class MaskSensitiveData
{
    public function __construct(
        public MaskingAlgorithm $algorithm,
        public bool             $preserveLength,
        public ?int             $visibleChars
    ) {}

    public function describeResponsibility() : string
    {
        return 'masks sensitive data using partial, full, hash, tokenize, or format-preserving masking.';
    }

    public static function partial(int $visibleChars = 4) : self
    {
        return new self(MaskingAlgorithm::PARTIAL, true, $visibleChars);
    }

    public static function full() : self
    {
        return new self(MaskingAlgorithm::FULL, false, 0);
    }

    public function mask(string $value) : string
    {
        if (empty($value)) {
            return '';
        }

        return match ($this->algorithm) {
            MaskingAlgorithm::FULL    => str_repeat('*', strlen($value)),
            MaskingAlgorithm::PARTIAL => $this->maskPartial($value),
            MaskingAlgorithm::HASH    => hash('sha256', $value),
            default                   => str_repeat('*', strlen($value)),
        };
    }

    private function maskPartial(string $value) : string
    {
        $visible = $this->visibleChars ?? 4;
        $length  = strlen($value);

        if ($length <= $visible) {
            return str_repeat('*', $length);
        }

        $masked      = str_repeat('*', $length - $visible);
        $visiblePart = substr($value, -$visible);

        return $masked . $visiblePart;
    }

    public function toMetadata() : array
    {
        return [
            'algorithm'       => $this->algorithm->value,
            'preserve_length' => $this->preserveLength,
            'visible_chars'   => $this->visibleChars,
        ];
    }
}