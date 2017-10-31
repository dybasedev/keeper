<?php
/**
 * ConnectionManager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase;


use Closure;

class ConnectionManager
{
    protected $connections = [];

    protected $connectionCreator = [];

    public function registerConnectionCreator($connection, Closure $callback)
    {

    }

    public function getConnection($connection = null)
    {

    }

    public function getDefaultConnection()
    {

    }
}