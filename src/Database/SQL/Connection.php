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
abstract class Connection
{
    use DetectsDeadlocks, DetectsLostConnections;

    /**
     * @var PDO
     */
    protected $pdoInstance;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var int
     */
    protected $transactions = 0;

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
     * @param       $statement
     * @param array $binder
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

            // We'll simply execute the given callback within a try / catch block and if we
            // catch any exception we can rollback this transaction so that none of this
            // gets actually persisted to a database or stored in a permanent fashion.
            try {
                return tap($callback($this), function () {
                    $this->commit();
                });
            }

                // If we catch an exception we'll rollback this transaction and try again if we
                // are not out of attempts. If we are out of attempts we will just throw the
                // exception back out and let the developer handle an uncaught exceptions.
            catch (Exception $e) {
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
        // We allow developers to rollback to a certain transaction level. We will verify
        // that this given transaction level is valid before attempting to rollback to
        // that level. If it's not we will just return out and not attempt anything.
        $toLevel = is_null($toLevel)
            ? $this->transactions - 1
            : $toLevel;

        if ($toLevel < 0 || $toLevel >= $this->transactions) {
            return;
        }

        // Next, we will actually perform this rollback within this database and fire the
        // rollback event. We will also set the current transaction level to the given
        // level that was passed into this method so it will be right from here out.
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