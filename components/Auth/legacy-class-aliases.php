<?php

declare(strict_types=1);

$legacyAliases = [
    'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\TokenCodecInterface'
    => 'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\Codec\\TokenCodecInterface',
    'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\HmacTokenCodec'
    => 'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\Codec\\HmacTokenCodec',
    'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\MultiKeyHmacTokenCodec'
    => 'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\Codec\\MultiKeyHmacTokenCodec',
    'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\RefreshAuthenticationRequest'
    => 'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\Flow\\RefreshAuthenticationRequest',
    'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\RefreshAuthenticationFailed'
    => 'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\Flow\\RefreshAuthenticationFailed',
    'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\IssuedToken'
    => 'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\Record\\IssuedToken',
    'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\IssuedRefreshToken'
    => 'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\Record\\IssuedRefreshToken',
    'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\RefreshTokenStoreInterface'
    => 'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\Store\\RefreshTokenStoreInterface',
    'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\TokenRevocationStoreInterface'
    => 'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\Store\\TokenRevocationStoreInterface',
    'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\InMemoryRefreshTokenStore'
    => 'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\Store\\InMemoryRefreshTokenStore',
    'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\InMemoryTokenRevocationStore'
    => 'Avax\\Auth\\System\\Capabilities\\Identity\\Tokens\\Runtime\\Store\\InMemoryTokenRevocationStore',
    'Avax\\Auth\\System\\Capabilities\\Identity\\Mfa\\Runtime\\Totp'
    => 'Avax\\Auth\\System\\Capabilities\\Identity\\Mfa\\Runtime\\Totp\\Totp',
    'Avax\\Auth\\System\\Capabilities\\Identity\\Mfa\\Runtime\\VerifyMfaChallengeData'
    => 'Avax\\Auth\\System\\Capabilities\\Identity\\Mfa\\Runtime\\Data\\VerifyMfaChallengeData',
];

foreach ($legacyAliases as $legacyClass => $currentClass) {
    if (
        ! class_exists(class: $legacyClass)
        && ! interface_exists(interface: $legacyClass)
        && (class_exists(class: $currentClass) || interface_exists(interface: $currentClass))
    ) {
        class_alias(class: $currentClass, alias: $legacyClass);
    }
}
