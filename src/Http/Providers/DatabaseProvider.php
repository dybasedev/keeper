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
use Dybasedev\Keeper\Http\ModuleProvider;

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

    }

    public function boot()
    {
    }

    public function alias()
    {
        return [
            'sql.db' => [ConnectionManager::class]
        ];
    }


}