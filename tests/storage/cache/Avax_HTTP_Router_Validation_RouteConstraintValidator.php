<?php

declare(strict_types=1);

use Avax\Components\Application\Container\Features\Think\Model\ServicePrototype;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Constraints\RouteConstraintValidator;
use Avax\Components\HTTP\Router\Validation\RouteConstraintValidator;

return ServicePrototype::__set_state(array: [
                                                'class'              => RouteConstraintValidator::class,
                                                'constructor'        => null,
                                                'injectedProperties' => [
                                                    ],
                                                'injectedMethods' => [
                                                    ],
                                                'isInstantiable'  => true,
                                            ]);
