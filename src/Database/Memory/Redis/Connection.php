<?php
/**
 * Connection.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Database\Memory\Redis;


use Redis;

abstract class Connection
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var Redis
     */
    protected $redisInstance;

    /**
     * Connection constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }


    abstract protected function createDriverInstance();

    public function connect()
    {
        if (!$this->redisInstance) {
            $this->redisInstance = $this->createDriverInstance();
        }
    }

    public function reconnect()
    {
        $this->disconnect();
        $this->connect();
    }

    public function disconnect()
    {
        $this->redisInstance = null;
    }
}