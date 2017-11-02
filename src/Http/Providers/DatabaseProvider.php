<?php
/**
 * DatabaseProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Providers;

use Dybasedev\Keeper\Data\SQLDatabase\Connection;
use Dybasedev\Keeper\Data\SQLDatabase\ConnectionManager;
use Dybasedev\Keeper\Data\SQLDatabase\Connections\MySQLConnection;
use Dybasedev\Keeper\Http\ModuleProvider;
use Illuminate\Contracts\Container\Container;

class DatabaseProvider extends ModuleProvider
{
    public function register()
    {
        $this->container->singleton('sql.db', function () {
            $manager = new ConnectionManager($this->config['storage.database']);

            $manager->registerConnectionCreator('mysql', function (array $options) {
                $connection = new MySQLConnection($options);
                $connection->setDispatcher($this->container['events']);
                return $connection;
            });

            return $manager;
        });

        $this->container->singleton('sql.db.connection', function (Container $container) {
            /** @var ConnectionManager $manager */
            $manager = $container['sql.db'];

            return $manager->getConnection();
        });

    }

    public function boot()
    {
    }

    public function alias()
    {
        return [
            'sql.db'            => [ConnectionManager::class],
            'sql.db.connection' => [Connection::class],
        ];
    }


}