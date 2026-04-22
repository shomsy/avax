<?php
$dirs = [
    '/home/shomsy/projects/components/Foundation/Auth/tests',
    '/home/shomsy/projects/components/Foundation/Auth/System',
];

$replacements = [
    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\HmacTokenCodec'             => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\HmacTokenCodec',
    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\MultiKeyHmacTokenCodec'     => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\MultiKeyHmacTokenCodec',
    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\FileBackedHmacKeyRingCodec' => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\FileBackedHmacKeyRingCodec',

    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\InMemoryRefreshTokenStore'     => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryRefreshTokenStore',
    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\InMemoryTokenRevocationStore'  => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryTokenRevocationStore',
    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshTokenStoreInterface'    => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface',
    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\TokenRevocationStoreInterface' => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\TokenRevocationStoreInterface',

    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\IssuedToken'        => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedToken',
    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\IssuedRefreshToken' => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedRefreshToken',
    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\ResolvedToken'      => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\ResolvedToken',
    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshTokenRecord' => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\RefreshTokenRecord',

    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\TokenIssuerInterface' => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Issuer\TokenIssuerInterface',

    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshAuthenticationRequest' => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthenticationRequest',
    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshAuthenticationFailed'  => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthenticationFailed',
    'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshAuthentication'        => 'Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthentication',

    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaEnrollmentFailed' => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\MfaEnrollmentFailed',
    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallengeFailed'  => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\MfaChallengeFailed',
    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaRecoveryFailed'   => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\MfaRecoveryFailed',
    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\FreshMfaRequired'    => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\FreshMfaRequired',

    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\StartMfaChallenge'           => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\StartMfaChallenge',
    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\VerifyMfaChallenge'          => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\VerifyMfaChallenge',
    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\InMemoryAttemptLimitStorage' => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\InMemoryAttemptLimitStorage',
    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\InMemoryMfaChallengeStore'   => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\InMemoryMfaChallengeStore',
    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\LimitMfaAttempts'            => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\LimitMfaAttempts',
    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\MfaChallengeRecord'          => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\MfaChallengeRecord',
    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\MfaChallengeStoreInterface'  => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\MfaChallengeStoreInterface',

    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\InMemoryMfaStore'    => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\InMemoryMfaStore',
    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaChallengePurpose' => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallengePurpose',
    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaMethod'           => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaMethod',
    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaStoreInterface'   => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaStoreInterface',
    'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\InMemoryMfaStore'          => 'Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\InMemoryMfaStore',
];

$count = 0;
foreach ($dirs as $baseDir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $path       = $file->getPathname();
            $content    = file_get_contents($path);
            $newContent = $content;

            foreach ($replacements as $old => $new) {
                $newContent = str_replace($old . ';', $new . ';', $newContent);
                $newContent = str_replace($old . ' ', $new . ' ', $newContent);
                $newContent = str_replace($old . ':', $new . ':', $newContent);
                $newContent = str_replace($old . '\\', $new . '\\', $newContent);
                $newContent = str_replace($old . "\n", $new . "\n", $newContent);
            }

            if ($content !== $newContent) {
                // To avoid duplicate identical imports if we accidentally map two old ones to the same new one
                $lines      = explode("\n", $newContent);
                $imports    = [];
                $finalLines = [];
                foreach ($lines as $line) {
                    if (str_starts_with($line, 'use ') && str_ends_with($line, ';')) {
                        if (! isset($imports[$line])) {
                            $imports[$line] = true;
                            $finalLines[]   = $line;
                        }
                    } else {
                        $finalLines[] = $line;
                    }
                }
                $newContent = implode("\n", $finalLines);

                file_put_contents($path, $newContent);
                $count++;
            }
        }
    }
}
echo "Fixed $count files.\n";
