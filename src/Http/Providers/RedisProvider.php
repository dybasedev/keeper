<?php
/**
 * RedisProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Providers;

use Dybasedev\Keeper\Data\Redis\RedisManager;
use Dybasedev\Keeper\Http\DestructibleModuleProvider;

class RedisProvider extends DestructibleModuleProvider
{
    public function register()
    {
        $this->container->singleton('redis', function () {
            $redis = new RedisManager($this->config['storage.redis'] ?? [
                    'host' => '127.0.0.1',
                    'port' => 6379,
                ]);

            $redis->setDefaultDatabase($this->config['storage.redis.default'] ?? 0);

            return $redis;
        });
    }

    public function boot()
    {
        //
    }

    public function destroy()
    {
        /** @var RedisManager $redis */
        $redis = $this->container['redis'];
        $redis->freeConnection();
    }


}