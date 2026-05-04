<?php

declare(strict_types=1);

return Avax\Container\Features\Think\Model\ServicePrototype::__set_state([
    'class' => 'Avax\\HTTP\\Middleware\\MiddlewareResolver',
    'constructor' => Avax\Container\Features\Think\Model\MethodPrototype::__set_state([
        'name' => '__construct',
        'parameters' => [
            0 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                'name' => 'groupResolver',
                'type' => 'Avax\\HTTP\\Middleware\\MiddlewareGroupResolver',
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
