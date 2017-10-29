<?php
/**
 * Connection.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase;


use Dybasedev\Keeper\Data\SQLDatabase\Exceptions\ConnectException;
use Dybasedev\Keeper\Data\SQLDatabase\Interfaces\ConnectionDriver;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DetectsLostConnections;
use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;

class Connection
{
    use DetectsLostConnections;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var Dispatcher
     */
    protected $eventDispatcher;

    /**
     * @var PreparationManager
     */
    protected $preparationManager;

    /**
     * @var PDO[]
     */
    protected $pdoInstances;

    /**
     * @var string
     */
    protected $defaultConnectionName;

    /**
     * Connection constructor.
     *
     * @param Container  $container
     * @param Repository $config
     * @param Dispatcher $eventDispatcher
     */
    public function __construct(Container $container, Repository $config, Dispatcher $eventDispatcher = null)
    {
        $this->container       = $container;
        $this->config          = $config;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function connect(string $connection = null)
    {
        $connection = $this->getConnectionName($connection);

        if (isset($this->pdoInstances[$connection])) {
            return true;
        }

        $config = $this->config->get('storage.database.connections.' . $connection);
        if (is_null($config)) {
            throw new InvalidArgumentException();
        }

        $driverName = $config['driver'];

        /** @var ConnectionDriver $driver */
        $driver = $this->container['sql.db.driver.' . $driverName];
        $driver->setConnectOptions($config);

        try {
            $this->pdoInstances[$connection] = $driver->createPdoInstance();
        } catch (PDOException $exception) {
            throw new ConnectException($exception->getMessage(), 0, $exception);
        }

        return true;
    }

    public function getConnectionName(string $connection = null)
    {
        if (is_null($connection)) {
            return $this->defaultConnectionName ?: $this->defaultConnectionName = $this->config['storage.database.default'];
        }

        return $connection;
    }

    public function reconnect(string $connection = null)
    {
        $connection = $this->getConnectionName($connection);

        if (isset($this->pdoInstances[$connection])) {
            unset($this->pdoInstances[$connection]);
        }

        return $this->connect($connection);
    }

    /**
     * 获取预处理查询语句资源管理器
     *
     * @return PreparationManager
     */
    public function getPreparationManager(): PreparationManager
    {
        return $this->preparationManager ?: $this->preparationManager = $this->container['sql.db.preparation'];
    }

    public function runPreparation(string $key, $bindParameters = [])
    {
        /**
         * @var PDOStatement $statement
         * @var ExecutablePreparation|QueriablePreparation $preparation
         */
        list($statement, $preparation) = $this->getPreparationManager()->getPreparation($key);

        if (is_null($preparation)) {
            throw new InvalidArgumentException();
        }

        $preparation->binder($statement, $bindParameters);

        try {
            return $statement->execute();
        } catch (PDOException $exception) {
            if ($this->causedByLostConnection($exception)) {
                $this->reconnect();

                return $statement->execute();
            }

            throw $exception;
        }
    }
}