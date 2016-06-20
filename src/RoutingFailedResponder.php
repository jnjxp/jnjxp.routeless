<?php
/**
 * Routeless Fail Responder
 *
 * PHP version 5
 *
 * Copyright (C) 2016 Jake Johns
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 *
 * @category  Responder
 * @package   Jnjxp\Routeless
 * @author    Jake Johns <jake@jakejohns.net>
 * @copyright 2016 Jake Johns
 * @license   http://jnj.mit-license.org/2016 MIT License
 * @link      http://github.com/jnjxp/jnjxp.routeless
 */

namespace Jnjxp\Routeless;

use Aura\Router\Route;

use Aura\Router\Rule\Allows;
use Aura\Router\Rule\Accepts;
use Aura\Router\Rule\Host;
use Aura\Router\Rule\Path;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Routing failed responder
 *
 * @category Responder
 * @package  Jnjxp\Routeless
 * @author   Jake Johns <jake@jakejohns.net>
 * @license  http://jnj.mit-license.org/ MIT License
 * @link     http://github.com/jnjxp/jnjxp.routeless
 */
class RoutingFailedResponder
{
    /**
     * Factories indexed by rule name
     *
     * @var array
     *
     * @access protected
     */
    protected $factories = [];

    /**
     * Default methods to respond with if no factory present
     *
     * @var array
     *
     * @access protected
     */
    protected $defaults = [
        Allows::class  => 'methodNotAllowed',
        Accepts::class => 'notAcceptable',
        Host::class    => 'notFound',
        Path::class    => 'notFound',
    ];

    /**
     * Create a routing failed responder
     *
     * @param array $factories array of rule name to responder factories
     *
     * @access public
     */
    public function __construct(array $factories = null)
    {
        if ($factories) {
            $this->factories = $factories;
        }
    }

    /**
     * Respond to route based on failed rule
     *
     * @param Request  $request  PSR7 Request
     * @param Response $response PSR7 Response
     * @param Route    $route    Failed rule
     *
     * @return Respone
     *
     * @access public
     */
    public function __invoke(
        Request $request,
        Response $response,
        Route $route
    ) {
        $responder = $this->getResponderForFailedRoute($route);
        return $responder($request, $response, $route);
    }

    /**
     * Does a responder exist for name?
     *
     * @param string $name Name of rule for which to check
     *
     * @return bool
     *
     * @access public
     */
    public function has($name)
    {
        return isset($this->factories[$name]);
    }

    /**
     * Set a factory for a name
     *
     * @param string   $name    Name of rule for responder
     * @param callable $factory Callable factory for responder
     *
     * @return $this
     *
     * @access public
     */
    public function set($name, callable $factory)
    {
        $this->factories[$name] = $factory;
        return $this;
    }

    /**
     * Get
     *
     * @param string $name Name of rule for which to get responder
     *
     * @return callable
     * @throws Exception if no factory available for rule
     *
     * @access public
     */
    public function get($name)
    {
        if (! $this->has($name)) {
            $message = sprintf('No responder for failed rule "%s"', $name);
            throw new Exception($message);
        }
        $factory = $this->factories[$name];
        return $factory();
    }

    /**
     * Get responder for failed route
     *
     * @param Route $route Failed Route
     *
     * @return callable
     *
     * @access protected
     */
    protected function getResponderForFailedRoute(Route $route)
    {
        $rule = $route->failedRule;
        if ($this->has($rule)) {
            return $this->get($rule);
        }

        if (isset($this->defaults[$rule])) {
            return [$this, $this->defaults[$rule]];
        }

        return [$this, 'other'];
    }

    /**
     * Method not allowed
     *
     * Builds the Response when the failed route method was not allowed.
     *
     * @param Request  $request  PSR7 Request
     * @param Response $response PSR7 Response
     * @param Route    $route    Failed Route
     *
     * @return Response
     *
     * @access protected
     */
    protected function methodNotAllowed(
        Request $request,
        Response $response,
        Route $route
    ) {
        $request;
        $response = $response->withStatus(405)
            ->withHeader('Allow', implode(', ', $route->allows))
            ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($route->allows));
        return $response;
    }

    /**
     * Not Acceptable
     *
     * Builds the Response when the failed route could not accept the media type.
     *
     * @param Request  $request  PSR7 Request
     * @param Response $response PSR7 Response
     * @param Route    $route    Failed Route
     *
     * @return Response
     *
     * @access protected
     */
    protected function notAcceptable(
        Request $request,
        Response $response,
        Route $route
    ) {
        $request;
        $response = $response
            ->withStatus(406)
            ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($route->accepts));
        return $response;
    }

    /**
     * Resource not found
     *
     * Builds the Response when the failed route host or path was not found.
     *
     * @param Request  $request  PSR7 Request
     * @param Response $response PSR7 Response
     * @param Route    $route    Failed Route
     *
     * @return Response
     *
     * @access protected
     */
    protected function notFound(Request $request, Response $response, Route $route)
    {
        $request; $route;
        $response = $response->withStatus(404);
        $response->getBody()->write('404 Not Found');
        return $response;
    }

    /**
     * Other rule failed
     *
     * Builds the Response when routing failed for some other reason.
     *
     * @param Request  $request  PSR7 Request
     * @param Response $response PSR7 Response
     * @param Route    $route    Failed Route
     *
     * @return Response
     *
     * @access protected
     */
    protected function other(Request $request, Response $response, Route $route)
    {
        $request;
        $response = $response->withStatus(500)
            ->withHeader('Content-Type', 'text/plain');
        $message = sprintf(
            'Route "%s" failed for rule "%s"',
            $route->name,
            $route->failedRule
        );
        $response->getBody()->write($message);
        return $response;
    }
}
