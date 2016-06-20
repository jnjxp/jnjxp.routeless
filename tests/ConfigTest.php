<?php
// @codingStandardsIgnoreFile

namespace Jnjxp\Routeless;

use Aura\Di\AbstractContainerConfigTest;
use Radar\Adr\Handler\RoutingHandler;

use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class ConfigTest extends AbstractContainerConfigTest
{
    protected function getConfigClasses()
    {
        $config = new Config(
            [ 'Foo' => 'Bar' ]
        );
        return [
            $config,
            'Radar\Adr\Config',
        ];
    }

    public function provideGet()
    {
        return [
            ['radar/adr:adr', 'Radar\Adr\Adr'],
        ];
    }

    public function provideNewInstance()
    {
        return [
            ['Radar\Adr\Handler\RoutingHandler']
        ];
    }

    public function testHandler()
    {
        $handler = $this->di->newInstance(RoutingHandler::class);

        $handler(
            ServerRequestFactory::fromGlobals(),
            new Response,
            [$this, 'nextHandler']
        );
    }

    public function nextHandler($request)
    {
        $action = $request->getAttribute('radar/adr:action');
        $responder = $action->getResponder();
        $this->assertEquals(
            RoutingFailedResponder::class,
            $responder
        );

        $responder = $this->di->newInstance($responder);

        $this->assertTrue($responder->has('Foo'));
    }
}
