<?php

use Avax\Container\Features\Think\Model\ServicePrototype;
use Avax\HTTP\Router\Support\RouteRegistry;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteRegistry;

return ServicePrototype::__set_state(array: [
                                                'class' => RouteRegistry::class,
                                                'constructor'        => null,
                                                'injectedProperties' =>
                                                    [
                                                    ],
                                                'injectedMethods'    =>
                                                    [
                                                    ],
                                                'isInstantiable'     => true,
                                            ]);
