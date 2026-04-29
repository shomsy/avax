<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Container\Capabilities\Providers\Runtime\Http;

use Avax\Tests\TestCase;
use components\Container\BindingBuilderInterface;
use components\Container\ContainerInterface;
use components\Container\DependencyInjection\Capability\Providers\Runtime\Http\ViewServiceProvider;
use components\Container\DependencyInjection\Configuration\Settings;
use components\View\BladeTemplateEngine;

final class ViewServiceProviderTest extends TestCase
{
    public function test_register_uses_defaults_without_base_path_helper() : void
    {
        $previousCwd   = getcwd();
        $temporaryBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'avax-view-provider-' . uniqid();
        $defaultCache  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'avax' . DIRECTORY_SEPARATOR . 'views';
        $registrations = [];
        $builder       = $this->createMock(BindingBuilderInterface::class);
        $builder->method('to')->willReturnSelf();
        $builder->method('tag')->willReturnSelf();
        $builder->method('withArguments')->willReturnSelf();
        $builder->method('withArgument')->willReturnSelf();

        mkdir(directory: $temporaryBase . DIRECTORY_SEPARATOR . 'Presentation' . DIRECTORY_SEPARATOR . 'Views', permissions: 0777, recursive: true);
        mkdir(directory: $defaultCache, permissions: 0777, recursive: true);
        chdir(directory: $temporaryBase);

        try {
            $container = $this->createMock(ContainerInterface::class);
            $container
                ->method('singleton')
                ->willReturnCallback(
                    callback: static function (string $abstract, mixed $concrete = null) use (&$registrations, $builder) : BindingBuilderInterface {
                        $registrations[$abstract] = $concrete;

                        return $builder;
                    }
                );
            $container
                ->method('get')
                ->with('config')
                ->willReturn(value: new Settings);

            $provider = new ViewServiceProvider(app: $container);
            $provider->register();

            $factory = $registrations[BladeTemplateEngine::class] ?? null;

            $this->assertIsCallable(actual: $factory);
            $this->assertInstanceOf(expected: BladeTemplateEngine::class, actual: $factory());
        } finally {
            if (is_string(value: $previousCwd) && $previousCwd !== '') {
                chdir(directory: $previousCwd);
            }
        }
    }
}
