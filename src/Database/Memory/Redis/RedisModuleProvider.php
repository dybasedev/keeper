<?php
/**
 * RedisModuleProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Database\Memory\Redis;

use Dybasedev\KeeperContracts\Module\ModuleProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;

class RedisModuleProvider implements ModuleProvider
{
    public function register(Container $container)
    {
        $container->instance(ConnectionManager::class,
            new ConnectionManager($container->make(Repository::class)->get('redis')));
    }

    public function mount(Container $container)
    {
        //
    }

}