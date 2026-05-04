<?php

declare(strict_types=1);

return Avax\Container\Features\Think\Model\ServicePrototype::__set_state([
    'class' => 'Avax\\HTTP\\Router\\Routing\\RoutePipelineFactory',
    'constructor' => Avax\Container\Features\Think\Model\MethodPrototype::__set_state([
        'name' => '__construct',
        'parameters' => [
            0 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                'name' => 'container',
                'type' => 'Avax\\Container\\Features\\Core\\Contracts\\ContainerInterface',
                'hasDefault' => false,
                'default' => null,
                'isVariadic' => false,
                'allowsNull' => false,
                'required' => true,
            ]),
            1 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                'name' => 'dispatcher',
                'type' => 'Avax\\HTTP\\Dispatcher\\ControllerDispatcher',
                'hasDefault' => false,
                'default' => null,
                'isVariadic' => false,
                'allowsNull' => false,
                'required' => true,
            ]),
            2 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                'name' => 'middlewareResolver',
                'type' => 'Avax\\HTTP\\Middleware\\MiddlewareResolver',
                'hasDefault' => false,
                'default' => null,
                'isVariadic' => false,
                'allowsNull' => false,
                'required' => true,
            ]),
            3 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                'name' => 'stageChain',
                'type' => 'Avax\\HTTP\\Router\\Routing\\StageChain',
                'hasDefault' => false,
                'default' => null,
                'isVariadic' => false,
                'allowsNull' => false,
                'required' => true,
            ]),
            4 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                'name' => 'logger',
                'type' => 'Psr\\Log\\LoggerInterface',
                'hasDefault' => true,
                'default' => Psr\Log\NullLogger::__set_state([
                ]),
                'isVariadic' => false,
                'allowsNull' => false,
                'required' => false,
            ]),
        ],
    ]),
    'injectedProperties' => [
    ],
    'injectedMethods' => [
    ],
    'isInstantiable' => true,
]);
