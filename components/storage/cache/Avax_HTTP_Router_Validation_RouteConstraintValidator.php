<?php

use components\Container\Features\Think\Model\ServicePrototype;
use components\HTTP\Router\System\Flows\ResolveRequest\Constraints\RouteConstraintValidator;
use components\HTTP\Router\Validation\RouteConstraintValidator;

return ServicePrototype::__set_state(array: [
                                                'class'              => RouteConstraintValidator::class,
                                                'constructor'        => null,
                                                'injectedProperties' =>
                                                    [
                                                    ],
                                                'injectedMethods'    =>
                                                    [
                                                    ],
                                                'isInstantiable'     => true,
                                            ]);
