<?php

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteMatcher;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Constraints\RouteConstraintValidator;
use Avax\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcher;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;

return ServicePrototype::__set_state(array: [
                                                'class'              => HttpRequestRouter::class,
                                                'constructor'        =>
                                                    MethodPrototype::__set_state(array: [
                                                                                            'name'       => '__construct',
                                                                                            'parameters' =>
                                                                                                [
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'constraintValidator',
                                                                                                                                               'type' => RouteConstraintValidator::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'matcher',
                                                                                                                                               'type' => RouteMatcher::class,
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
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => true,
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
