<?php
/**
 * Connection.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Database\SQL;

use Closure;
use Exception;
use Dybasedev\Keeper\Database\Connection as BaseConnection;
use Illuminate\Database\DetectsDeadlocks;
use Illuminate\Database\DetectsLostConnections;
use PDO;
use PDOStatement;
use Throwable;

/**
 * Database connection
 *
 * Note:
 * Transaction control code is copied from Laravel/Illuminate database component.
 *
 * @package Dybasedev\Keeper\Database\SQL
 */
abstract class Connection extends BaseConnection
{
    use DetectsDeadlocks, DetectsLostConnections;

    /**
     * @var PDO
     */
    protected $pdoInstance;

    /**
     * @var int
     */
    protected $transactions = 0;

    /**
     * Create PDO instance
     *
     * @return PDO
     */
    abstract protected function createDriverInstance(): PDO;

    public function connect()
    {
        if (!$this->pdoInstance) {
            $this->pdoInstance = $this->createDriverInstance();
        }
    }

    public function reconnect()
    {
        $this->disconnect();
        $this->connect();
    }

    public function disconnect()
    {
        $this->pdoInstance = null;
    }

    /**
     * @param         $statement
     * @param Closure $callback
     *
     * @return mixed
     * @throws Throwable
     */
    public function process($statement, Closure $callback)
    {
        $prepared = $this->pdoInstance->prepare($statement);

        try {
            $result = ($callback)($prepared);
        } catch (Throwable $e) {
            if ($this->transactions >= 1) {
                throw $e;
            }

            $result = $this->checkIfLostConnection($e, function () use ($prepared, $callback) {
                ($callback)($prepared);
            });
        }

        return $result;
    }

    /**
     * @param                $statement
     * @param array|callable $binder
     *
     * @return mixed
     * @throws Throwable
     */
    public function execute($statement, $binder = [])
    {
        return $this->process($statement, function (PDOStatement $statement) use ($binder) {
            $this->bindValues($statement, $binder);

            return $statement->execute();
        });
    }

    /**
     * @param PDOStatement  $statement
     * @param array|Closure $binder
     */
    protected function bindValues(PDOStatement $statement, $binder)
    {
        if (is_null($binder)) {
            return;
        }

        if (is_array($binder)) {
            $binder = function (PDOStatement $statement) use ($binder) {
                foreach ($binder as $key => $value) {
                    $statement->bindValue(
                        is_string($key) ? $key : $key + 1, $value,
                        is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
                    );
                }
            };
        }

        $binder($statement);
    }

    /**
     * @param       $statement
     * @param array $binder
     * @param null  $fetcher
     *
     * @return mixed
     * @throws Throwable
     */
    public function select($statement, $binder = [], $fetcher = null)
    {
        return $this->process($statement, function (PDOStatement $prepared) use ($binder, $fetcher) {
            $this->bindValues($prepared, $binder);
            $prepared->execute();

            if (is_null($fetcher)) {
                return $prepared->fetchAll();
            }

            return ($fetcher)($prepared);
        });
    }

    /**
     * @param string $modelName
     * @param        $statement
     * @param array  $binder
     * @param null   $fetcher
     *
     * @return mixed
     * @throws Throwable
     */
    public function selectModel(string $modelName, $statement, $binder = [], $fetcher = null)
    {
        return $this->process($statement, function (PDOStatement $prepared) use ($binder, $fetcher, $modelName) {
            $this->bindValues($prepared, $binder);
            $prepared->execute();

            if (is_null($fetcher)) {
                return $prepared->fetchAll(PDO::FETCH_CLASS, $modelName);
            }

            return ($fetcher)($prepared, $modelName);
        });
    }

    /**
     * Get last insert id
     *
     * @param null $name
     *
     * @return string|int
     */
    public function lastInsertId($name = null)
    {
        return $this->getDriverInstance()->lastInsertId($name);
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure $callback
     * @param  int      $attempts
     *
     * @return mixed
     *
     * @throws \Exception|\Throwable
     */
    public function transaction(Closure $callback, $attempts = 1)
    {
        for ($currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++) {
            $this->beginTransaction();

            try {
                return tap($callback($this), function () {
                    $this->commit();
                });
            } catch (Exception $e) {
                $this->handleTransactionException(
                    $e, $currentAttempt, $attempts
                );
            } catch (Throwable $e) {
                $this->rollBack();

                throw $e;
            }
        }
    }

    /**
     * Handle an exception encountered when running a transacted statement.
     *
     * @param  \Exception $e
     * @param  int        $currentAttempt
     * @param  int        $maxAttempts
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function handleTransactionException($e, $currentAttempt, $maxAttempts)
    {
        if ($this->causedByDeadlock($e) &&
            $this->transactions > 1) {
            --$this->transactions;

            throw $e;
        }

        $this->rollBack();

        if ($this->causedByDeadlock($e) &&
            $currentAttempt < $maxAttempts) {
            return;
        }

        throw $e;
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     * @throws \Exception
     */
    public function beginTransaction()
    {
        $this->createTransaction();

        ++$this->transactions;
    }

    /**
     * @return PDO
     */
    public function getDriverInstance()
    {
        return $this->pdoInstance;
    }

    /**
     * @param Exception $e
     * @param Closure   $callback
     *
     * @return mixed
     * @throws Exception
     */
    public function checkIfLostConnection($e, Closure $callback)
    {
        if ($this->causedByLostConnection($e)) {
            $this->reconnect();

            return ($callback)();
        }

        throw $e;
    }

    abstract protected function createSavePoint();

    abstract protected function rollbackSavePoint($toLevel);

    /**
     * Create a transaction within the database.
     *
     * @return void
     * @throws Exception
     */
    protected function createTransaction()
    {
        if ($this->transactions == 0) {
            try {
                $this->getDriverInstance()->beginTransaction();
            } catch (Exception $e) {
                $this->checkIfLostConnection($e, function () {
                    $this->getDriverInstance()->beginTransaction();
                });
            }
        } elseif ($this->transactions >= 1) {
            $this->createSavepoint();
        }
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        if ($this->transactions == 1) {
            $this->getDriverInstance()->commit();
        }

        $this->transactions = max(0, $this->transactions - 1);
    }

    /**
     * Rollback the active database transaction.
     *
     * @param  int|null $toLevel
     *
     * @return void
     */
    public function rollBack($toLevel = null)
    {
        $toLevel = is_null($toLevel)
            ? $this->transactions - 1
            : $toLevel;

        if ($toLevel < 0 || $toLevel >= $this->transactions) {
            return;
        }

        $this->performRollBack($toLevel);

        $this->transactions = $toLevel;
    }

    /**
     * Perform a rollback within the database.
     *
     * @param  int $toLevel
     *
     * @return void
     */
    protected function performRollBack($toLevel)
    {
        if ($toLevel == 0) {
            $this->getDriverInstance()->rollBack();
        } else {
            $this->rollbackSavePoint($toLevel + 1);
        }
    }

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel()
    {
        return $this->transactions;
    }
}