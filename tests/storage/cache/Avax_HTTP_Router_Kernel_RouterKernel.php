<?php

use components\Container\Features\Think\Model\MethodPrototype;
use components\Container\Features\Think\Model\ParameterPrototype;
use components\Container\Features\Think\Model\ServicePrototype;
use components\HTTP\Router\Kernel\RouterKernel;
use components\HTTP\Router\Routing\HttpRequestRouter;
use components\HTTP\Router\Routing\RouteExecutor;
use components\HTTP\Router\Routing\RoutePipelineFactory;
use components\HTTP\Router\Support\HeadRequestFallback;
use components\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use components\HTTP\Router\System\Flows\RunRoute\Dispatch\RouteExecutor;
use components\HTTP\Router\System\Flows\RunRoute\Pipeline\RoutePipelineFactory;
use components\HTTP\Router\System\Flows\RunRoute\RouterKernel;

return ServicePrototype::__set_state(array: [
                                                'class'              => RouterKernel::class,
                                                'constructor'        =>
                                                    MethodPrototype::__set_state(array: [
                                                                                            'name'       => '__construct',
                                                                                            'parameters' =>
                                                                                                [
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'httpRequestRouter',
                                                                                                                                               'type' => HttpRequestRouter::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'pipelineFactory',
                                                                                                                                               'type' => RoutePipelineFactory::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'headRequestFallback',
                                                                                                                                               'type' => HeadRequestFallback::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'routeExecutor',
                                                                                                                                               'type' => RouteExecutor::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
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
