<?php

use Avax\Container\Features\Think\Model\ServicePrototype;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Constraints\RouteConstraintValidator;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;

return ServicePrototype::__set_state(array: [
                                                'class' => RouteConstraintValidator::class,
                                                'constructor'        => null,
                                                'injectedProperties' =>
                                                    [
                                                    ],
                                                'injectedMethods'    =>
                                                    [
                                                    ],
                                                'isInstantiable'     => true,
                                            ]);
