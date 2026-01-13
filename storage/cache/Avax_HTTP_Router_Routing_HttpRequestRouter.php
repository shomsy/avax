<?php

return \Avax\Container\Features\Think\Model\ServicePrototype::__set_state(array(
   'class' => 'Avax\\HTTP\\Router\\Routing\\HttpRequestRouter',
   'constructor' => 
  \Avax\Container\Features\Think\Model\MethodPrototype::__set_state(array(
     'name' => '__construct',
     'parameters' => 
    array (
      0 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'constraintValidator',
         'type' => 'Avax\\HTTP\\Router\\Validation\\RouteConstraintValidator',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      1 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'matcher',
         'type' => 'Avax\\HTTP\\Router\\Matching\\RouteMatcherInterface',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      2 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'logger',
         'type' => 'Psr\\Log\\LoggerInterface',
         'hasDefault' => true,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => true,
         'required' => false,
      )),
      3 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'trace',
         'type' => 'Avax\\HTTP\\Router\\Tracing\\RouterTrace',
         'hasDefault' => true,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => true,
         'required' => false,
      )),
    ),
  )),
   'injectedProperties' => 
  array (
  ),
   'injectedMethods' => 
  array (
  ),
   'isInstantiable' => true,
));
