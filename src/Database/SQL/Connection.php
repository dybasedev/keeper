<?php
/**
 * Connection.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Database\SQL;

use Closure;
use PDO;
use PDOStatement;

/**
 * Database connection
 *
 * @package Dybasedev\Keeper\Database\SQL
 */
abstract class Connection
{
    /**
     * @var PDO
     */
    protected $pdoInstance;

    /**
     * @var array
     */
    protected $options;

    /**
     * Create PDO instance
     *
     * @return PDO
     */
    abstract protected function createPdoInstance(): PDO;

    public function connect()
    {
        if (!$this->pdoInstance) {
            $this->pdoInstance = $this->createPdoInstance();
        }
    }

    public function reconnect()
    {
        $this->close();
        $this->connect();
    }

    public function close()
    {
        $this->pdoInstance = null;
    }

    public function process($statement, $binds, Closure $callback)
    {
        try {

            $result = $this->runQueryCallback($query, $bindings, $callback);
        } catch (QueryException $e) {
            $result = $this->handleQueryException(
                $e, $query, $bindings, $callback
            );
        }
    }
}