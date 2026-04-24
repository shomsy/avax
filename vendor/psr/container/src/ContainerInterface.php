<?php
namespace Psr\Container;
/**
 * Container Interface.
 */
interface ContainerInterface
{
    public function get($id);
    public function has($id): bool;
}
