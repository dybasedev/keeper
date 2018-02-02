<?php
/**
 * Connection.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Database;


abstract class Connection
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Connection constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    abstract public function connect();

    abstract public function disconnect();

    abstract public function reconnect();
}