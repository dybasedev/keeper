<?php
/**
 * EloquentDatabaseProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Providers;


use Dybasedev\Keeper\Http\ModuleProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Queue\EntityResolver;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Eloquent\QueueEntityResolver;

class EloquentDatabaseProvider extends ModuleProvider
{
    public function register()
    {
        Model::clearBootedModels();

        $this->registerConnectionServices();
        $this->registerQueueableEntityResolver();
    }

    public function boot()
    {
        $this->config->set('database', $this->config['storage.database']);

        Model::setConnectionResolver($this->container['db']);
        Model::setEventDispatcher($this->container['events']);
    }

    public function alias()
    {
        return [
            'db'            => [DatabaseManager::class],
            'db.connection' => [Connection::class, ConnectionInterface::class,],
        ];
    }

    /**
     * Register the primary database bindings.
     *
     * @return void
     */
    protected function registerConnectionServices()
    {
        $this->container->singleton('db.factory', function (Container $container) {
            return new ConnectionFactory($container);
        });

        $this->container->singleton('db', function (Container $container) {
            return new DatabaseManager($container, $container['db.factory']);
        });

        $this->container->bind('db.connection', function (Container $container) {
            return $container['db']->connection();
        });
    }

    /**
     * Register the queueable entity resolver implementation.
     *
     * @return void
     */
    protected function registerQueueableEntityResolver()
    {
        $this->container->singleton(EntityResolver::class, function () {
            return new QueueEntityResolver;
        });
    }

}