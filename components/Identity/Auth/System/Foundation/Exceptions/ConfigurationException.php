<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation\Exceptions;

use SensitiveParameter;
use Throwable;

final class ConfigurationException extends AuthException
{
    /**
     * @param array<string, scalar|list<string>|null> $context
     */
    public function __construct(
        string                  $message,
        #[SensitiveParameter]
        private readonly string $errorCode = 'auth.configuration.invalid',
        private readonly array  $context = [],
        int $code = 0, Throwable|null $previous = null,
    )
    {
        parent::__construct(message: $message, code: $code, previous: $previous);
    }

    public static function missingUserSource(string $buildPath = 'AuthBuilder::ready()') : self
    {
        return new self(
            message  : $buildPath . ' requires a user source. Call forUser() before ready().',
            errorCode: 'auth.configuration.user_source_missing',
            context  : [
                           'build_path' => $buildPath,
                           'option'     => 'forUser()',
                       ],
        );
    }

    public static function missingIdentity(string $buildPath = 'AuthBuilder::ready()') : self
    {
        return new self(
            message  : $buildPath . ' requires an identity coordinator. Call withIdentity() or withIdentityBackends() before ready().',
            errorCode: 'auth.configuration.identity_missing',
            context  : [
                           'build_path' => $buildPath,
                           'options'    => ['withIdentity()', 'withIdentityBackends()'],
                       ],
        );
    }

    public static function missingIdentityBackend(string|null $buildPath = null,
        string  $hint = 'Provide at least one session or JWT backend.',
    ) : self
    {
        $buildPath ??= 'AuthBuilder::ready()';

        return new self(
            message  : sprintf('%s requires at least one identity backend (session or JWT). %s', $buildPath, $hint),
            errorCode: 'auth.configuration.identity_backend_missing',
            context  : [
                           'build_path' => $buildPath,
                           'hint'       => $hint,
                       ],
        );
    }

    public static function enterpriseSessionRegistryRequired(string $buildPath = 'AuthBuilder::ready()') : self
    {
        return new self(
            message  : $buildPath . ' cannot enable [enterprise_mode] because [session_registry] is missing. Use withSessionRegistry() to provide a SQL or Redis session registry.',
            errorCode: 'auth.configuration.dependency_missing',
            context  : [
                           'build_path'  => $buildPath,
                           'capability'  => 'enterprise_mode',
                           'requirement' => 'session_registry',
                           'option'      => 'withSessionRegistry()',
                       ],
        );
    }

    public static function missingDependency(
        string $dependency,
        string $hint = '',
        string $buildPath = 'AuthBuilder::ready()',
    ) : self
    {
        $message = $buildPath . ' requires [' . $dependency . '].';
        if ($hint !== '') {
            $message .= ' Call ' . $hint . '.';
        }

        return new self(
            message  : $message,
            errorCode: 'auth.configuration.dependency_missing',
            context  : [
                           'build_path' => $buildPath,
                           'dependency' => $dependency,
                           'hint'       => $hint,
                       ],
        );
    }

    public static function missingCapabilityDependency(
        string $capability,
        string $requirement,
        string $buildPath,
        string $option,
        string $cause,
    ) : self
    {
        return new self(
            message  : sprintf('%s cannot enable [%s] because [%s] is missing. %s Provide %s or remove the capability-specific configuration.', $buildPath, $capability, $requirement, $cause, $option),
            errorCode: 'auth.configuration.dependency_missing',
            context  : [
                           'build_path'  => $buildPath,
                           'capability'  => $capability,
                           'requirement' => $requirement,
                           'option'      => $option,
                           'cause'       => $cause,
                       ],
        );
    }

    public function errorCode() : string
    {
        return $this->errorCode;
    }

    /**
     * @return array<string, scalar|list<string>|null>
     */
    public function context() : array
    {
        return $this->context;
    }
}
