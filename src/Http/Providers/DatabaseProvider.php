<?php
/**
 * DatabaseProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Providers;

use Dybasedev\Keeper\Data\SQLDatabase\ConnectionManager;
use Dybasedev\Keeper\Data\SQLDatabase\Connections\MySQLConnection;
use Dybasedev\Keeper\Data\SQLDatabase\PreparationManager;
use Dybasedev\Keeper\Http\ModuleProvider;
use Illuminate\Contracts\Container\Container;

class DatabaseProvider extends ModuleProvider
{
    public function register()
    {
        $this->container->singleton('sql.db', function () {
            $manager = new ConnectionManager($this->config['storage.database']);

            $manager->registerConnectionCreator('mysql', function (array $options) {
                return new MySQLConnection($options);
            });
        });

        $this->container->singleton('sql.db.driver.mysql', MySQLConnection::class);

        $this->container->singleton('sql.db.preparation', function (Container $container) {
            return new PreparationManager($container['sql.db']);
        });
    }

    public function boot()
    {
    }

    public function alias()
    {
        return [

        ];
    }


}