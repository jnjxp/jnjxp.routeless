<?php
// @codingStandardsIgnoreFile

namespace Jnjxp\Routeless;

use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Aura\Router\Route;

use Aura\Router\Rule\Allows;
use Aura\Router\Rule\Accepts;
use Aura\Router\Rule\Host;
use Aura\Router\Rule\Path;

class RoutingFailedResponderTest extends \PHPUnit_Framework_TestCase
{
    protected $request;

    protected $response;

    protected $route;

    protected $responder;

    public function setUp()
    {
        $this->request = ServerRequestFactory::fromGlobals();
        $this->response = new Response;
        $this->route = new Route;
        $this->responder = new RoutingFailedResponder($this->getFactories());

        $barFactory = function () {
            return [$this, 'barResponder'];
        };

        $this->responder->set('bar', $barFactory);

        $this->route->name('MyRoute');
        $this->route->accepts('foo/bar');
        $this->route->allows('GET');
    }

    public function getFactories()
    {
        $fooFactory = function () {
            return [$this, 'fooResponder'];
        };

        return [
            'foo' => $fooFactory,
        ];
    }

    public function respond()
    {
        $responder = $this->responder;
        $this->response = $responder(
            $this->request,
            $this->response,
            $this->route
        );
        return $this;
    }

    protected function assertSameInput($request, $response, $route)
    {
        $this->assertSame($this->request, $request);
        $this->assertSame($this->response, $response);
        $this->assertSame($this->route, $route);
    }

    public function fooResponder($request, $response, $route)
    {
        $this->assertSameInput($request, $response, $route);
        $response->getBody()->write('foo');
        return $response;
    }

    public function barResponder($request, $response, $route)
    {
        $this->assertSameInput($request, $response, $route);
        $response->getBody()->write('bar');
        return $response;
    }

    public function assertStatusCode($code)
    {
        $this->assertEquals($code, $this->response->getStatusCode());
        return $this;
    }

    public function assertBody($body)
    {
        $this->assertEquals($body, (string) $this->response->getBody());
        return $this;
    }

    public function assertHeader($name, $content)
    {
        $this->assertEquals(
            $content,
            $this->response->getHeaderLine($name)
        );
        return $this;
    }

    public function testNoResponder()
    {
        $this->setExpectedException(
            'Jnjxp\Routeless\Exception',
            'No responder for failed rule "Bing"'
        );
        $this->responder->get('Bing');
    }

    public function responseProvider()
    {
        $allowHead = [
            'Allow' => 'GET',
            'Content-Type' => 'application/json',
        ];

        $acceptHead = [
            'Content-Type' => 'application/json',
        ];

        $otherHead = [
            'Content-Type' => 'text/plain'
        ];

        return [
            [Allows::class, 405, '["GET"]', $allowHead],
            [Accepts::class, 406, '["foo\/bar"]', $acceptHead],
            [Host::class, 404, '404 Not Found'],
            [Path::class, 404, '404 Not Found'],
            ['foo', 200, 'foo'],
            ['bar', 200, 'bar'],
            ['baz', 500, 'Route "MyRoute" failed for rule "baz"', $otherHead]
        ];
    }

    /**
     * @dataProvider responseProvider
     */
    public function testResponder($rule, $code, $body, $headers = null)
    {
        $this->route->failedRule($rule);
        $this->respond()
            ->assertStatusCode($code)
            ->assertBody($body);

        $headers = (array) $headers;
        foreach ($headers as $name => $value) {
            $this->assertHeader($name, $value);
        }
    }
}
