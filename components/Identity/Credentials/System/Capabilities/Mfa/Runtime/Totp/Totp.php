<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp;

use DateTimeImmutable;
use InvalidArgumentException;
use Random\RandomException;
use SensitiveParameter;

/**
 * RFC 6238 TOTP implementation owned by the package.
 */
final readonly class Totp implements TotpInterface
{
    private int $periodSeconds;

    private int $digits;

    public function __construct(
        ?int $digits = null,
        ?int $periodSeconds = null,
        private int $allowedSkewSteps = 1,
    ) {
        $digits ??= 6;
        $periodSeconds ??= 30;
        $this->digits = $digits;
        $this->periodSeconds = $periodSeconds;
        if ($this->digits < 6) {
            throw new InvalidArgumentException(message: 'TOTP digits must be at least 6.');
        }

        if ($this->periodSeconds < 1) {
            throw new InvalidArgumentException(message: 'TOTP period must be at least 1 second.');
        }

        if ($this->allowedSkewSteps < 0) {
            throw new InvalidArgumentException(message: 'Allowed skew steps cannot be negative.');
        }
    }

    /**
     * @throws RandomException
     */
    public function generateSecret(): string
    {
        return $this->base32Encode(bytes: random_bytes(length: 20));
    }

    private function base32Encode(string $bytes): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        $length = strlen(string: $bytes);

        for ($index = 0; $index < $length; $index++) {
            $binary .= str_pad(string: decbin(num: ord(character: $bytes[$index])), length: 8, pad_string: '0', pad_type: STR_PAD_LEFT);
        }

        $chunks = str_split(string: $binary, length: 5);
        $encoded = '';

        foreach ($chunks as $chunk) {
            $characterIndex = (int) bindec(binary_string: str_pad(string: $chunk, length: 5, pad_string: '0', pad_type: STR_PAD_RIGHT));
            $character = substr(string: $alphabet, offset: $characterIndex, length: 1);

            if ($character === '') {
                throw new InvalidArgumentException(message: 'TOTP base32 encoding failed.');
            }

            $encoded .= $character;
        }

        return $encoded;
    }

    public function provisioningUri(string $issuer, #[SensitiveParameter] string $accountLabel, #[SensitiveParameter] string $secret): string
    {
        $issuer = trim(string: $issuer);
        $accountLabel = trim(string: $accountLabel);

        if ($issuer === '' || $accountLabel === '') {
            throw new InvalidArgumentException(message: 'Issuer and account label are required for MFA enrollment.');
        }

        $label = rawurlencode(string: sprintf('%s:%s', $issuer, $accountLabel));
        $issuerQuery = rawurlencode(string: $issuer);
        $secretQuery = rawurlencode(string: $secret);

        return sprintf('otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=%d&period=%d', $label, $secretQuery, $issuerQuery, $this->digits, $this->periodSeconds);
    }

    public function verify(
        #[SensitiveParameter]
        string $secret,
        #[SensitiveParameter]
        string $code,
        DateTimeImmutable $moment,
        ?int $lastAcceptedTimeStep = null,
    ): TotpVerification {
        if (preg_match(pattern: '/^\d{6,8}$/', subject: $code) !== 1) {
            return TotpVerification::invalid(reason: 'format_invalid');
        }

        $secretBytes = $this->base32Decode(secret: $secret);

        if ($secretBytes === null) {
            return TotpVerification::invalid(reason: 'secret_invalid');
        }

        $currentTimeStep = intdiv(num1: $moment->getTimestamp(), num2: $this->periodSeconds);

        for ($offset = -1 * $this->allowedSkewSteps; $offset <= $this->allowedSkewSteps; $offset++) {
            $timeStep = $currentTimeStep + $offset;

            if ($timeStep < 0) {
                continue;
            }

            if ($lastAcceptedTimeStep !== null && $timeStep <= $lastAcceptedTimeStep) {
                continue;
            }

            $expected = $this->hotp(secret: $secretBytes, counter: $timeStep);

            if (hash_equals(known_string: $expected, user_string: $code)) {
                return TotpVerification::accepted(timeStep: $timeStep);
            }
        }

        if ($lastAcceptedTimeStep !== null) {
            $currentCode = $this->hotp(secret: $secretBytes, counter: $currentTimeStep);

            if (hash_equals(known_string: $currentCode, user_string: $code) && $currentTimeStep <= $lastAcceptedTimeStep) {
                return TotpVerification::invalid(reason: 'replayed');
            }
        }

        return TotpVerification::invalid();
    }

    private function base32Decode(#[SensitiveParameter] string $secret): ?string
    {
        $alphabet = array_flip(array: str_split(string: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'));
        $normalized = strtoupper(string: preg_replace(pattern: '/[^A-Z2-7]/', replacement: '', subject: $secret) ?? '');

        if ($normalized === '') {
            return null;
        }

        $bits = '';
        $length = strlen(string: $normalized);

        for ($index = 0; $index < $length; $index++) {
            $character = $normalized[$index];

            if (! isset($alphabet[$character])) {
                return null;
            }

            $bits .= str_pad(string: decbin(num: $alphabet[$character]), length: 5, pad_string: '0', pad_type: STR_PAD_LEFT);
        }

        $bytes = '';

        foreach (str_split(string: $bits, length: 8) as $chunk) {
            if (strlen(string: $chunk) < 8) {
                continue;
            }

            $codePoint = (int) bindec(binary_string: $chunk);

            if ($codePoint < 0 || $codePoint > 255) {
                throw new InvalidArgumentException(message: 'TOTP base32 decoding failed.');
            }

            $bytes .= chr(codepoint: $codePoint);
        }

        return $bytes;
    }

    private function hotp(#[SensitiveParameter] string $secret, int $counter): string
    {
        $binaryCounter = pack('N2', ($counter >> 32) & 0xFFFFFFFF, $counter & 0xFFFFFFFF);
        $hash = hash_hmac(algo: 'sha1', data: $binaryCounter, key: $secret, binary: true);
        $offset = ord(character: $hash[19]) & 0x0F;
        $chunk = substr(string: $hash, offset: $offset, length: 4);
        $unpacked = unpack(format: 'N', string: $chunk);

        if ($unpacked === false || ! isset($unpacked[1])) {
            throw new InvalidArgumentException(message: 'TOTP hash unpack failed.');
        }

        $value = $unpacked[1] & 0x7FFFFFFF;
        $modulo = 10 ** $this->digits;

        return str_pad(string: (string) ($value % $modulo), length: $this->digits, pad_string: '0', pad_type: STR_PAD_LEFT);
    }

    public function codeAt(#[SensitiveParameter] string $secret, DateTimeImmutable $moment): string
    {
        $secretBytes = $this->base32Decode(secret: $secret);

        if ($secretBytes === null) {
            throw new InvalidArgumentException(message: 'TOTP secret is invalid.');
        }

        $timeStep = intdiv(num1: $moment->getTimestamp(), num2: $this->periodSeconds);

        return $this->hotp(secret: $secretBytes, counter: $timeStep);
    }
}
