<?php

declare(strict_types=1);

return Avax\Container\Features\Think\Model\ServicePrototype::__set_state([
    'class' => 'Avax\\HTTP\\Router\\Routing\\RouterRegistrar',
    'constructor' => Avax\Container\Features\Think\Model\MethodPrototype::__set_state([
        'name' => '__construct',
        'parameters' => [
            0 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                'name' => 'registry',
                'type' => 'Avax\\HTTP\\Router\\Support\\RouteRegistry',
                'hasDefault' => false,
                'default' => null,
                'isVariadic' => false,
                'allowsNull' => false,
                'required' => true,
            ]),
            1 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                'name' => 'httpRequestRouter',
                'type' => 'Avax\\HTTP\\Router\\Routing\\HttpRequestRouter',
                'hasDefault' => false,
                'default' => null,
                'isVariadic' => false,
                'allowsNull' => false,
                'required' => true,
            ]),
        ],
    ]),
    'injectedProperties' => [
    ],
    'injectedMethods' => [
    ],
    'isInstantiable' => true,
]);
