<?php
/**
 * Manager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Session;


use Dybasedev\Keeper\Http\Interfaces\SessionDriver;

class Manager
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var SessionDriver
     */
    protected $driver;

    /**
     * Manager constructor.
     *
     * @param array         $config
     * @param SessionDriver $driver
     */
    public function __construct(array $config, SessionDriver $driver)
    {
        $this->config = $config;
        $this->driver = $driver;
    }


}