<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition\Compilation;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolvePlan;

/**
 * Emits deterministic PHP methods for compiled service resolution.
 */
final class MethodEmitter
{
    /**
     * Derives one stable method name from one service id.
     */
    public function methodNameFor(string $serviceId): string
    {
        return 'service_'.substr(string: sha1(string: $serviceId), offset: 0, length: 16);
    }

    /**
     * Emits a fallback method that delegates back to dynamic resolution.
     */
    public function emitDynamicMethod(string $methodName): string
    {
        return <<<PHP
            public function {$methodName}(\\Avax\\Container\\Capabilities\\Resolution\\ResolveDependency \$resolver, \\Avax\\Container\\Capabilities\\Resolution\\ResolveRequest \$request, array \$overrides = []) : mixed
            {
                return \$resolver->resolveDynamicRequest(\$request);
            }
            PHP;
    }

    /**
     * Emits one direct service-construction method for the compiled artifact.
     *
     * @param  array<string, mixed>  $registrationArguments
     */
    public function emitDirectMethod(
        string $methodName,
        string $serviceId,
        string $class, ResolvePlan|null $resolvePlan,
        array $registrationArguments,
        bool $needsFinish,
    ): string {
        $className = '\\'.ltrim(string: $class, characters: '\\');
        $arguments = $this->emitArguments(serviceId: $serviceId, plan: $resolvePlan);
        $compiledRegistrationArguments = $registrationArguments
                |> serialize(...)
                |> base64_encode(...)
                |> $this(...);

        $body = <<<PHP
            public function {$methodName}(\\Avax\\Container\\Capabilities\\Resolution\\ResolveDependency \$resolver, \\Avax\\Container\\Capabilities\\Resolution\\ResolveRequest \$request, array \$overrides = []) : mixed
            {
                \$arguments = \\array_replace(
                    \\unserialize(\\base64_decode({$compiledRegistrationArguments}), ['allowed_classes' => false]),
                    \$overrides
                );
            PHP;

        if ($arguments === []) {
            $body .= PHP_EOL.sprintf('        $instance = new %s();', $className).PHP_EOL;
        } else {
            $body .= PHP_EOL.sprintf('        $instance = new %s(', $className).PHP_EOL;

            foreach ($arguments as $index => $argument) {
                $suffix = $index === array_key_last(array: $arguments) ? '' : ',';
                $body .= '            '.$argument.$suffix.PHP_EOL;
            }

            $body .= '        );'.PHP_EOL;
        }

        if ($needsFinish) {
            return $body.<<<PHP
                    return \$resolver->finishCompiledService(
                        {$this->export(value: $serviceId)},
                        \$instance,
                        {$this->export(value: $class)},
                        \$request,
                        \$overrides
                    );
                }
                PHP;
        }

        return $body.<<<'PHP'
                return $instance;
            }
            PHP;
    }

    /**
     * @return list<string>
     */
    private function emitArguments(ResolvePlan|null $resolvePlan, string $serviceId) : array
    {
        if (! $resolvePlan instanceof ResolvePlan || $resolvePlan->isEmpty()) {
            return [];
        }

        $arguments = [];

        foreach ($resolvePlan->parameters as $parameter) {
            $expression = 'array_key_exists('.$this->export(value: $parameter['name']).', $arguments)'
                .' ? $arguments['.$this->export(value: $parameter['name']).']';

            if ($parameter['serviceId'] === null) {
                $expression .= ' : (array_key_exists('.$this->export(value: $parameter['name']).', $request->context)'
                    .' ? $request->context['.$this->export(value: $parameter['name']).']'
                    .' : '.$this->fallbackExpression(parameter: $parameter, serviceId: $serviceId).')';
            } else {
                $expression .= ' : '.$this->fallbackExpression(parameter: $parameter, serviceId: $serviceId);
            }

            $arguments[] = '('.$expression.')';
        }

        return $arguments;
    }

    private function export(mixed $value): string
    {
        return var_export(value: $value, return: true);
    }

    /**
     * @param array{name: string, serviceId: string|null, hasDefault: bool, default: string, allowsNull: bool}
     * $parameter
     */
    private function fallbackExpression(array $parameter, string $serviceId): string
    {
        if ($parameter['serviceId'] !== null) {
            return '$resolver->resolveCompiledDependency('
                .$this->export(value: $parameter['serviceId'])
                .', $request)';
        }

        if ($parameter['hasDefault']) {
            return '\\unserialize(\\base64_decode('
                .$this->export(value: $parameter['default'])
                ."), ['allowed_classes' => false])";
        }

        if ($parameter['allowsNull']) {
            return 'null';
        }

        return 'throw new \\Avax\\Container\\Capabilities\\Diagnostics\\Errors\\ContainerException('
            .$this->export(value: sprintf('Cannot resolve parameter [$%s] for service [%s].', $parameter['name'], $serviceId))
            .')';
    }
}
