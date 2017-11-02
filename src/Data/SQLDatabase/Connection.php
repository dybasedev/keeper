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
     *
     * @return PDOStatement
     */
    public function statementProcess(string $statement, $bindings = [])
    {
        $prepared = $this->makePreparedStatement($statement);
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
     *
     * @return PDOStatement
     */
    public function runStatement(string $statement, $bindings = [])
    {
        try {
            return $this->statementProcess($statement, $bindings);
        } catch (PDOException $exception) {
            if ($this->causedByLostConnection($exception)) {
                $this->reconnect();

                return $this->statementProcess($statement, $bindings);
            }

            throw $exception;
        }
    }

    /**
     * 执行一条语句，返回该语句执行后影响的行数
     *
     * @param string $statement
     * @param array  $bindings
     *
     * @return int
     */
    public function execute(string $statement, $bindings = [])
    {
        $prepared = $this->runStatement($statement, $bindings);

        return $prepared->rowCount();
    }

    /**
     * 执行一条写入（插入）语句，返回该语句执行后产生的 ID
     *
     * @param string $statement
     * @param array  $bindings
     *
     * @return string
     */
    public function insert(string $statement, $bindings = [])
    {
        $this->runStatement($statement, $bindings);

        return $this->getPdoInstance()->lastInsertId();
    }

    /**
     *
     *
     * @param string $statement
     * @param array  $bindings
     *
     * @return PDOStatement
     */
    public function query(string $statement, $bindings = [])
    {
        return $this->runStatement($statement, $bindings);
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
        $hash = $this->hashStatement($statement);

        return $this->preparations[$hash] ?? $this->preparations[$hash] = $this->getPdoInstance()->prepare($statement);
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