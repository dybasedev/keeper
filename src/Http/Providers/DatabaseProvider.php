<?php
/**
 * DatabaseProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Providers;

use Dybasedev\Keeper\Data\SQLDatabase\PreparationManager;
use Dybasedev\Keeper\Http\ModuleProvider;
use Illuminate\Contracts\Container\Container;

class DatabaseProvider extends ModuleProvider
{
    public function register()
    {
        $this->container->singleton('sql.db', function () {

        });

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