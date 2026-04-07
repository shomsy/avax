<?php

declare(strict_types=1);

namespace Avax\Container\Compilation;

use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolvePlan;

final class MethodEmitter
{
    public function methodNameFor(string $serviceId) : string
    {
        return 'service_' . substr(sha1($serviceId), 0, 16);
    }

    public function emitDynamicMethod(string $methodName) : string
    {
        return <<<PHP
    public function {$methodName}(\\Avax\\Container\\DependencyInjection\\Dependencies\\Resolution\\ServiceResolver \$resolver, \\Avax\\Container\\DependencyInjection\\Dependencies\\Resolution\\ResolveRequest \$request, array \$overrides = []) : mixed
    {
        return \$resolver->resolveDynamicRequest(\$request);
    }

PHP;
    }

    public function emitDirectMethod(
        string $methodName,
        string $serviceId,
        string $class,
        ResolvePlan|null $plan,
        array $registrationArguments,
        bool $needsFinish
    ) : string {
        $className = '\\' . ltrim($class, '\\');
        $arguments = $this->emitArguments(plan: $plan, serviceId: $serviceId);
        $compiledRegistrationArguments = $this->export(base64_encode(serialize($registrationArguments)));

        $body = <<<PHP
    public function {$methodName}(\\Avax\\Container\\DependencyInjection\\Dependencies\\Resolution\\ServiceResolver \$resolver, \\Avax\\Container\\DependencyInjection\\Dependencies\\Resolution\\ResolveRequest \$request, array \$overrides = []) : mixed
    {
        \$arguments = \\array_replace(
            \\unserialize(\\base64_decode({$compiledRegistrationArguments}), ['allowed_classes' => true]),
            \$overrides
        );
PHP;

        if ($arguments === []) {
            $body .= PHP_EOL . "        \$instance = new {$className}();" . PHP_EOL;
        } else {
            $body .= PHP_EOL . "        \$instance = new {$className}(" . PHP_EOL;

            foreach ($arguments as $index => $argument) {
                $suffix = $index === array_key_last($arguments) ? '' : ',';
                $body .= '            ' . $argument . $suffix . PHP_EOL;
            }

            $body .= "        );" . PHP_EOL;
        }

        if ($needsFinish) {
            $body .= <<<PHP

        return \$resolver->finishCompiledService(
            {$this->export($serviceId)},
            \$instance,
            {$this->export($class)},
            \$request,
            \$overrides
        );
    }

PHP;

            return $body;
        }

        $body .= <<<PHP

        return \$instance;
    }

PHP;

        return $body;
    }

    /**
     * @return list<string>
     */
    private function emitArguments(ResolvePlan|null $plan, string $serviceId) : array
    {
        if ($plan === null || $plan->isEmpty()) {
            return [];
        }

        $arguments = [];

        foreach ($plan->parameters as $parameter) {
            $expression = 'array_key_exists(' . $this->export($parameter['name']) . ', $arguments)'
                . ' ? $arguments[' . $this->export($parameter['name']) . ']'
                . ' : ' . $this->fallbackExpression(parameter: $parameter, serviceId: $serviceId);

            $arguments[] = '(' . $expression . ')';
        }

        return $arguments;
    }

    /**
     * @param array{name: string, serviceId: string|null, hasDefault: bool, default: string, allowsNull: bool} $parameter
     */
    private function fallbackExpression(array $parameter, string $serviceId) : string
    {
        if ($parameter['serviceId'] !== null) {
            return '$resolver->resolveCompiledDependency('
                . $this->export($parameter['serviceId'])
                . ', $request)';
        }

        if ($parameter['hasDefault']) {
            return '\\unserialize(\\base64_decode('
                . $this->export($parameter['default'])
                . '), [\'allowed_classes\' => true])';
        }

        if ($parameter['allowsNull']) {
            return 'null';
        }

        return 'throw new \\Avax\\Container\\Errors\\ContainerException('
            . $this->export("Cannot resolve parameter [\${$parameter['name']}] for service [{$serviceId}].")
            . ')';
    }

    private function export(mixed $value) : string
    {
        return var_export($value, true);
    }
}
