<?php
/**
 * Router.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Routing\Interfaces;

use Dybasedev\Keeper\Routing\RouteRegister;

interface Router
{
    /**
     * @param string $method
     * @param string $uri
     *
     * @return array
     */
    public function dispatch($method, $uri);

    /**
     * @param array|null $data
     *
     * @return $this
     */
    public function mount(array $data = null);

    /**
     * @param string|RouteRegister $register
     *
     * @return $this
     */
    public function add($register);
}