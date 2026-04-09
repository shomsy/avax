<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * RFC 6238 TOTP implementation owned by the package.
 */
final readonly class Totp implements TotpInterface
{
    public function __construct(
        private int $digits = 6,
        private int $periodSeconds = 30,
        private int $allowedSkewSteps = 1
    )
    {
        if ($this->digits < 6) {
            throw new InvalidArgumentException('TOTP digits must be at least 6.');
        }

        if ($this->periodSeconds < 1) {
            throw new InvalidArgumentException('TOTP period must be at least 1 second.');
        }

        if ($this->allowedSkewSteps < 0) {
            throw new InvalidArgumentException('Allowed skew steps cannot be negative.');
        }
    }

    public function generateSecret() : string
    {
        return $this->base32Encode(random_bytes(20));
    }

    private function base32Encode(string $bytes) : string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary   = '';
        $length   = strlen($bytes);

        for ($index = 0; $index < $length; $index++) {
            $binary .= str_pad(decbin(ord($bytes[$index])), 8, '0', STR_PAD_LEFT);
        }

        $chunks  = str_split($binary, 5);
        $encoded = '';

        foreach ($chunks as $chunk) {
            if ($chunk === '') {
                continue;
            }

            $encoded .= $alphabet[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $encoded;
    }

    public function provisioningUri(string $issuer, string $accountLabel, string $secret) : string
    {
        $issuer       = trim($issuer);
        $accountLabel = trim($accountLabel);

        if ($issuer === '' || $accountLabel === '') {
            throw new InvalidArgumentException('Issuer and account label are required for MFA enrollment.');
        }

        $label       = rawurlencode("{$issuer}:{$accountLabel}");
        $issuerQuery = rawurlencode($issuer);
        $secretQuery = rawurlencode($secret);

        return "otpauth://totp/{$label}?secret={$secretQuery}&issuer={$issuerQuery}&algorithm=SHA1&digits={$this->digits}&period={$this->periodSeconds}";
    }

    public function verify(
        string            $secret,
        string            $code,
        DateTimeImmutable $moment,
        int|null          $lastAcceptedTimeStep = null
    ) : TotpVerification
    {
        if (! preg_match('/^\d{6,8}$/', $code)) {
            return TotpVerification::invalid('format_invalid');
        }

        $secretBytes = $this->base32Decode($secret);

        if ($secretBytes === null) {
            return TotpVerification::invalid('secret_invalid');
        }

        $currentTimeStep = intdiv($moment->getTimestamp(), $this->periodSeconds);

        for ($offset = -1 * $this->allowedSkewSteps; $offset <= $this->allowedSkewSteps; $offset++) {
            $timeStep = $currentTimeStep + $offset;

            if ($timeStep < 0) {
                continue;
            }

            if ($lastAcceptedTimeStep !== null && $timeStep <= $lastAcceptedTimeStep) {
                continue;
            }

            $expected = $this->hotp($secretBytes, $timeStep);

            if (hash_equals($expected, $code)) {
                return TotpVerification::accepted($timeStep);
            }
        }

        if ($lastAcceptedTimeStep !== null) {
            $currentCode = $this->hotp($secretBytes, $currentTimeStep);

            if (hash_equals($currentCode, $code) && $currentTimeStep <= $lastAcceptedTimeStep) {
                return TotpVerification::invalid('replayed');
            }
        }

        return TotpVerification::invalid();
    }

    private function base32Decode(string $secret) : string|null
    {
        $alphabet   = array_flip(str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'));
        $normalized = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret) ?? '');

        if ($normalized === '') {
            return null;
        }

        $bits   = '';
        $length = strlen($normalized);

        for ($index = 0; $index < $length; $index++) {
            $character = $normalized[$index];

            if (! isset($alphabet[$character])) {
                return null;
            }

            $bits .= str_pad(decbin($alphabet[$character]), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';

        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) < 8) {
                continue;
            }

            $bytes .= chr(bindec($chunk));
        }

        return $bytes;
    }

    private function hotp(string $secret, int $counter) : string
    {
        $binaryCounter = pack('N2', ($counter >> 32) & 0xFFFFFFFF, $counter & 0xFFFFFFFF);
        $hash          = hash_hmac('sha1', $binaryCounter, $secret, true);
        $offset        = ord($hash[19]) & 0x0F;
        $chunk         = substr($hash, $offset, 4);
        $value         = unpack('N', $chunk)[1] & 0x7FFFFFFF;
        $modulo        = 10 ** $this->digits;

        return str_pad((string) ($value % $modulo), $this->digits, '0', STR_PAD_LEFT);
    }

    public function codeAt(string $secret, DateTimeImmutable $moment) : string
    {
        $secretBytes = $this->base32Decode($secret);

        if ($secretBytes === null) {
            throw new InvalidArgumentException('TOTP secret is invalid.');
        }

        $timeStep = intdiv($moment->getTimestamp(), $this->periodSeconds);

        return $this->hotp($secretBytes, $timeStep);
    }
}
