<?php

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;
use Psr\Log\NullLogger;

return ServicePrototype::__set_state(array: [
    'class'              => 'Avax\\HTTP\\Router\\Routing\\RoutePipelineFactory',
    'constructor'        =>
        MethodPrototype::__set_state(array: [
            'name'       => '__construct',
            'parameters' =>
                [
                    0 =>
                        ParameterPrototype::__set_state(array: [
                            'name'       => 'container',
                            'type'       => 'Avax\\Container\\Features\\Core\\Contracts\\ContainerInterface',
                            'hasDefault' => false,
                            'default'    => null,
                            'isVariadic' => false,
                            'allowsNull' => false,
                            'required'   => true,
                        ]),
                    1 =>
                        ParameterPrototype::__set_state(array: [
                            'name'       => 'dispatcher',
                            'type'       => 'Avax\\HTTP\\Dispatcher\\ControllerDispatcher',
                            'hasDefault' => false,
                            'default'    => null,
                            'isVariadic' => false,
                            'allowsNull' => false,
                            'required'   => true,
                        ]),
                    2 =>
                        ParameterPrototype::__set_state(array: [
                            'name'       => 'middlewareResolver',
                            'type'       => 'Avax\\HTTP\\Middleware\\MiddlewareResolver',
                            'hasDefault' => false,
                            'default'    => null,
                            'isVariadic' => false,
                            'allowsNull' => false,
                            'required'   => true,
                        ]),
                    3 =>
                        ParameterPrototype::__set_state(array: [
                            'name'       => 'stageChain',
                            'type'       => 'Avax\\HTTP\\Router\\Routing\\StageChain',
                            'hasDefault' => false,
                            'default'    => null,
                            'isVariadic' => false,
                            'allowsNull' => false,
                            'required'   => true,
                        ]),
                    4 =>
                        ParameterPrototype::__set_state(array: [
                            'name'       => 'logger',
                            'type'       => 'Psr\\Log\\LoggerInterface',
                            'hasDefault' => true,
                            'default'    =>
                                NullLogger::__set_state([
                                ]),
                            'isVariadic' => false,
                            'allowsNull' => false,
                            'required'   => false,
                        ]),
                ],
        ]),
    'injectedProperties' =>
        [
        ],
    'injectedMethods'    =>
        [
        ],
    'isInstantiable'     => true,
]);
