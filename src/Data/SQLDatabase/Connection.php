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
     * @var PDOStatement[]
     */
    protected $preparations = [];

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


    /**
     * 连接
     *
     * @return $this
     */
    public function connect()
    {
        if (!$this->pdoInstance) {
            $this->pdoInstance = $this->createPdoInstance();
        }

        return $this;
    }

    /**
     * @return Connection
     */
    public function reconnect()
    {
        $this->pdoInstance = null;
        $this->cleanConnection();

        return $this->connect();
    }

    /**
     * 语句执行过程
     *
     * @param string $statement
     * @param array  $bindings
     * @param bool   $checkPreparationCache
     *
     * @return PDOStatement
     */
    public function statementProcess(string $statement, $bindings = [], $checkPreparationCache = true)
    {
        if ($checkPreparationCache) {
            $hash = $this->hashStatement($statement);

            if (isset($this->preparations[$hash])) {
                $prepared = $this->preparations[$hash];
            } else {
                $prepared = $this->makePreparedStatement($statement);
                $this->preparations[$hash] = $prepared;
            }
        } else {
            $prepared = $this->makePreparedStatement($statement);
        }

        $prepared->execute($bindings);

        return $prepared;
    }

    /**
     * 获取语句摘要
     *
     * @param string $statement
     *
     * @return string
     */
    public function hashStatement(string $statement)
    {
        return md5($statement);
    }

    /**
     * 执行语句
     *
     * @param string $statement
     * @param array  $bindings
     * @param bool   $checkPreparationCache
     *
     * @return PDOStatement
     */
    public function runStatement(string $statement, $bindings = [], $checkPreparationCache = true)
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

    /**
     * 执行一条语句，返回该语句执行后影响的行数
     *
     * @param string $statement
     * @param array  $bindings
     * @param bool   $cachePreparationCache
     *
     * @return int
     */
    public function execute(string $statement, $bindings = [], $cachePreparationCache = true)
    {
        $prepared = $this->runStatement($statement, $bindings, $cachePreparationCache);

        return $prepared->rowCount();
    }

    /**
     * 执行一条写入（插入）语句，返回该语句执行后产生的 ID
     *
     * @param string $statement
     * @param array  $bindings
     * @param bool   $checkPreparationCache
     *
     * @return string
     */
    public function insert(string $statement, $bindings = [], $checkPreparationCache = true)
    {
        $this->runStatement($statement, $bindings, $checkPreparationCache);

        return $this->getPdoInstance()->lastInsertId();
    }

    /**
     *
     *
     * @param string $statement
     * @param array  $bindings
     * @param bool   $cachePreparationCache
     *
     * @return PDOStatement
     */
    public function query(string $statement, $bindings = [], $cachePreparationCache = true)
    {
        return $this->runStatement($statement, $bindings, $cachePreparationCache);
    }


    /**
     * 构造 PDO 语句文件
     *
     * @param string $statement
     *
     * @return PDOStatement
     */
    protected function makePreparedStatement(string $statement)
    {
        return $this->getPdoInstance()->prepare($statement);
    }

    /**
     * @return PDO
     */
    public function getPdoInstance()
    {
        if (!$this->pdoInstance) {
            throw new ConnectException();
        }

        return $this->pdoInstance;
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