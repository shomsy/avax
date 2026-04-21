<?php

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;
use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Middleware\MiddlewareResolver;
use Avax\HTTP\Router\Routing\RoutePipelineFactory;
use Avax\HTTP\Router\Routing\StageChain;
use Psr\Log\NullLogger;

return ServicePrototype::__set_state(array: [
                                                'class'              => RoutePipelineFactory::class,
                                                'constructor'        =>
                                                    MethodPrototype::__set_state(array: [
                                                                                            'name'       => '__construct',
                                                                                            'parameters' =>
                                                                                                [
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'container',
                                                                                                                                               'type'       => 'Avax\\Container\\Features\\Core\\Contracts\\ContainerInterface',
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'dispatcher',
                                                                                                                                               'type' => ControllerDispatcher::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'middlewareResolver',
                                                                                                                                               'type' => MiddlewareResolver::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'stageChain',
                                                                                                                                               'type' => StageChain::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
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
