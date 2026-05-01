<?php

declare(strict_types=1);

use Avax\Components\Application\Container\Features\Think\Model\MethodPrototype;
use Avax\Components\Application\Container\Features\Think\Model\ParameterPrototype;
use Avax\Components\Application\Container\Features\Think\Model\ServicePrototype;
use Avax\Components\HTTP\Router\Kernel\RouterKernel;
use Avax\Components\HTTP\Router\Router;
use Avax\Components\HTTP\Router\Routing\ErrorResponses;
use Avax\Components\HTTP\Router\Routing\HttpRequestRouter;
use Avax\Components\HTTP\Router\Support\FallbackManager;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\Components\HTTP\Router\System\Flows\RunRoute\Responses\ErrorResponses;
use Avax\Components\HTTP\Router\System\Flows\RunRoute\RouterKernel;

return ServicePrototype::__set_state(array: [
                                                'class'              => Router::class,
                                                'constructor'        => MethodPrototype::__set_state(array: [
                                                                                            'name'       => '__construct',
                                                                                            'parameters' => [
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
                                                                                                                                               'name'       => 'kernel',
                                                                                                                                               'type' => RouterKernel::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'fallbackManager',
                                                                                                                                               'type' => FallbackManager::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                    ParameterPrototype::__set_state(array: [
                                                                                                                                               'name'       => 'errorFactory',
                                                                                                                                               'type' => ErrorResponses::class,
                                                                                                                                               'hasDefault' => false,
                                                                                                                                               'default'    => null,
                                                                                                                                               'isVariadic' => false,
                                                                                                                                               'allowsNull' => false,
                                                                                                                                               'required'   => true,
                                                                                                                                           ]),
                                                                                                ],
                                                                                        ]),
                                                'injectedProperties' => [
                                                    ],
                                                'injectedMethods'    => [
                                                    ],
                                                'isInstantiable'     => true,
                                            ]);
