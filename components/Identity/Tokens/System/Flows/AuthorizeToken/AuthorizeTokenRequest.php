<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Flows\AuthorizeToken;

use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Code\AuthorizationCodeStoreInterface;
use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use stdClass;

final readonly class AuthorizeTokenRequest
{
    public function __construct(
        private AuthorizationCodeStoreInterface $authorizationCodeStore,
        private DateInterval                    $dateInterval = new DateInterval(duration: 'PT5M'),
    ) {}

    /**
     * @param array<string, mixed> $request
     */
    public function execute(array $request) : stdClass
    {
        $subject = $request['subject'] ?? $request['sub'] ?? $request['user_id'] ?? null;

        if (! is_string(value: $subject) && ! is_int(value: $subject)) {
            throw new InvalidArgumentException(message: 'Token authorization requires a subject, sub, or user_id value.');
        }

        $clientId                = $request['client_id'] ?? null;
        $redirectUri             = $request['redirect_uri'] ?? null;
        $state                   = $request['state'] ?? null;
        $scopes                  = $this->normalizeScopes(scopes: $request['scope'] ?? $request['scopes'] ?? []);
        $expiresAt               = new DateTimeImmutable()->add(interval: $this->dateInterval);
        $authorizationCodeRecord = $this->authorizationCodeStore->create(
            subject    : (string) $subject,
            expiresAt  : $expiresAt,
            clientId   : is_string(value: $clientId) ? $clientId : null,
            scopes     : $scopes,
            redirectUri: is_string(value: $redirectUri) ? $redirectUri : null,
            state      : is_string(value: $state) ? $state : null,
        );

        return (object) [
            'code'       => $authorizationCodeRecord->code,
            'token_type' => 'authorization_code',
            'expires_at' => $authorizationCodeRecord->expiresAt,
            'state'      => $authorizationCodeRecord->state,
        ];
    }

    /**
     * @return list<string>
     */
    private function normalizeScopes(mixed $scopes) : array
    {
        if (is_string(value: $scopes)) {
            return array_values(array: array_filter(
                                           array   : preg_split(pattern: '/\s+/', subject: trim(string: $scopes)) ?: [],
                                           callback: static fn (string $scope) : bool => $scope !== '',
                                       ));
        }

        if (! is_array(value: $scopes)) {
            return [];
        }

        return array_values(array: array_filter(
                                       array   : $scopes,
                                       callback: static fn (mixed $scope) : bool => is_string(value: $scope) && $scope !== '',
                                   ));
    }
}
