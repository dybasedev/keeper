<?php
/**
 * Connection.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase;


use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;
use PDO;
use PDOStatement;

class Connection
{
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
        if (is_null($connection)) {
            $connection = $this->config['storage.database.default'];
        }

        if (isset($this->pdoInstances[$connection])) {
            return true;
        }

        $config = $this->config->get('storage.database.connections.' . $connection);
        if (is_null($config)) {
            throw new InvalidArgumentException();
        }

        //
    }

    public function reconnect(string $connection = null)
    {

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

        return $statement->execute();
    }
}