<?php
/**
 * SessionProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Providers;


use Dybasedev\Keeper\Data\Redis\RedisManager;
use Dybasedev\Keeper\Http\ModuleProvider;
use Dybasedev\Keeper\Http\Session\Drivers\RedisDriver;
use Dybasedev\Keeper\Http\Session\Manager;
use Illuminate\Contracts\Container\Container;

class SessionProvider extends ModuleProvider
{
    public function register()
    {
        $this->container->singleton('session.driver.redis', function (Container $container) {
            /** @var RedisManager $redisConnection */
            $redisConnection = $container->make('redis');

            return new RedisDriver($redisConnection->select($this->config['session.drivers.redis.db'] ?: 0));
        });

        $this->container->singleton('session', function (Container $container) {
            $session = new Manager($this->config['session'],
                $container->make('session.driver.' . $this->config->get('session.driver', 'redis')), $container);

            return $session;
        });

        $this->container->alias('session', Manager::class);
    }

    public function boot()
    {

    }

}