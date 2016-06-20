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
 * @category  Config
 * @package   Jnjxp\Routeless
 * @author    Jake Johns <jake@jakejohns.net>
 * @copyright 2016 Jake Johns
 * @license   http://jnj.mit-license.org/2016 MIT License
 * @link      http://github.com/jnjxp/jnjxp.routeless
 */

namespace Jnjxp\Routeless;

use Aura\Di\Container;
use Aura\Di\ContainerConfig;

use Radar\Adr\Handler\RoutingHandler;

/**
 * Config
 *
 * @category Config
 * @package  Jnjxp\Routeless
 * @author   Jake Johns <jake@jakejohns.net>
 * @license  http://jnj.mit-license.org/ MIT License
 * @link     http://github.com/jnjxp/jnjxp.routeless
 */
class Config extends ContainerConfig
{
    /**
     * Rules
     *
     * @var array
     *
     * @access protected
     */
    protected $rules = [];

    /**
     * __construct
     *
     * @param array $rules Map of rules
     *
     * @access public
     */
    public function __construct(array $rules = null)
    {
        if ($rules) {
            $this->setRules($rules);
        }
    }

    /**
     * SetRules
     *
     * @param array $rules Map of rules
     *
     * @return mixed
     *
     * @access public
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Define
     *
     * @param Container $di DI Container
     *
     * @return void
     *
     * @access public
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function define(Container $di)
    {
        $di->params[RoutingHandler::class]
            ['failResponder'] = RoutingFailedResponder::class;

        foreach ($this->rules as $rule => $responder) {
            $di->params[RoutingFailedResponder::class]
                ['factories'][$rule] = $di->lazyNew($responder);
        }
    }
}
