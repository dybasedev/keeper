<?php
/**
 * Connection.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase;

use Closure;
use Illuminate\Database\DetectsLostConnections;
use PDO;
use PDOStatement;
use PDOException;

abstract class Connection
{
    use DetectsLostConnections;

    /**
     * @var PDO
     */
    protected $pdoInstance;

    /**
     * @var array|PDOStatement[]
     */
    protected $preparations = [];

    /**
     * @var array
     */
    protected $options;

    public function connect()
    {
        if ($this->pdoInstance) {
            return $this->pdoInstance;
        }

        return $this->pdoInstance = $this->createPdoInstance();
    }

    public function reconnect()
    {
        $this->pdoInstance = null;
        $this->cleanConnection();

        return $this->connect();
    }

    public function query()
    {
        
    }

    protected function cleanConnection()
    {
        $this->preparations = [];
        gc_collect_cycles();
    }

    public function transaction(Closure $callback)
    {
        // transaction process
    }

    abstract protected function createPdoInstance(): PDO;
}