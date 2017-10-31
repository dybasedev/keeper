<?php
/**
 * Connection.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase;

use Closure;
use Dybasedev\Keeper\Data\SQLDatabase\Exceptions\ConnectException;
use Dybasedev\Keeper\Data\SQLDatabase\Exceptions\QueryException;
use Illuminate\Database\DetectsLostConnections;
use PDO;
use PDOException;
use PDOStatement;

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

    public function statementProcess($statement, $bindings = [], $checkPreparationCache = true)
    {
        if ($checkPreparationCache) {
            $hash = md5($statement);

            if (isset($this->preparations[$hash])) {
                $prepared = $this->preparations[$hash];
            } else {
                $prepared = $this->makePreparedStatement($statement);
                $this->preparations[$hash] = $prepared;
            }
        } else {
            $prepared = $this->makePreparedStatement($statement);
        }

        $result = $prepared->execute($bindings);

        if (!$result) {
            throw new QueryException($this->getPdoInstance()->errorInfo()[2], $this->getPdoInstance()->errorCode());
        }

        return $prepared;
    }

    public function runStatement($statement, $bindings = [], $checkPreparationCache = true)
    {
        try {
            return $this->statementProcess($statement, $bindings, $checkPreparationCache);
        } catch (PDOException $exception) {
            if ($this->causedByLostConnection($exception)) {
                $this->reconnect();
                return $this->statementProcess($statement, $bindings, $checkPreparationCache);
            }

            throw $exception;
        }
    }

    public function execute($statement, $bindings = [], $cachePreparationCache = true)
    {
        $prepared = $this->runStatement($statement, $bindings, $cachePreparationCache);

        return $prepared->rowCount();
    }

    public function insert($statement, $bindings = [], $checkPreparationCache = true)
    {
        $this->runStatement($statement, $bindings, $checkPreparationCache);

        return $this->getPdoInstance()->lastInsertId();
    }

    public function query($statement, $bindings = [], $cachePreparationCache = true)
    {
        return $this->runStatement($statement, $bindings, $cachePreparationCache);
    }

    protected function makePreparedStatement($statement)
    {
        return $this->getPdoInstance()->prepare($statement);
    }

    public function getPdoInstance()
    {
        if (!$this->pdoInstance) {
            throw new ConnectException();
        }

        return $this->pdoInstance;
    }

    public function selectStatement($statement)
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