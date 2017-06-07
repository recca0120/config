<?php

namespace Recca0120\Config\Tests;

use stdClass;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use Recca0120\Config\ConfigServiceProvider;
use Recca0120\Config\Repositories\DatabaseRepository;

class ConfigServiceProviderTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $container = m::mock(new Container);
        $container->instance('path.storage', __DIR__);
        $container->shouldReceive('databasePath')->andReturn(__DIR__);
        Container::setInstance($container);
    }

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testRegister()
    {
        $serviceProvider = new ConfigServiceProvider(
            $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );

        $app->shouldReceive('singleton')->once()->with('Recca0120\Config\Contracts\Repository', m::on(function ($closure) use ($app) {
            $app->shouldReceive('offsetGet')->once()->with('config')->andReturn(
                $config = m::mock('Illuminate\Contracts\Config\Repository')
            );
            $app->shouldReceive('make')->once()->with('Recca0120\Config\Config')->andReturn(
                $model = m::mock('Recca0120\Config\Config')
            );
            $app->shouldReceive('offsetGet')->once()->with('files')->andReturn(
                $files = m::mock('Illuminate\Filesystem\Filesystem')
            );

            $object = new stdClass;
            $object->value = [];
            $config->shouldReceive('all')->once()->andReturn([]);
            $files->shouldReceive('exists')->once()->andReturn(false);
            $model->shouldReceive('firstOrCreate')->andReturn($object);
            $files->shouldReceive('put')->once();
            $databaseRepository = $closure($app);

            return $databaseRepository instanceof DatabaseRepository;
        }));

        $serviceProvider->register();
        $this->assertSame(['config'], $serviceProvider->provides());
    }

    public function testBoot()
    {
        $serviceProvider = new ConfigServiceProvider(
            $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );

        $app->shouldReceive('runningInConsole')->once()->andReturn(true);

        $kernel = m::mock('\Illuminate\Contracts\Http\Kernel');
        $kernel->shouldReceive('pushMiddleware')->once()->with('Recca0120\Config\Middleware\SwapConfig');

        $this->assertNull($serviceProvider->boot($kernel));
    }
}
