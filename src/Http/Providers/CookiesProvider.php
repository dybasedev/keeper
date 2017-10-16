<?php
/**
 * CookiesProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Providers;


use Dybasedev\Keeper\Http\ModuleProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Cookie\CookieJar;

class CookiesProvider extends ModuleProvider
{
    public function register()
    {
        $this->container->singleton('cookie', function (Container $container) {
            $config = $container->make('config')->get('session');

            return (new CookieJar)->setDefaultPathAndDomain(
                $config['path'], $config['domain'], $config['secure'], $config['same_site'] ?? null
            );
        });

        $this->container->alias('cookie', CookieJar::class);
    }

    public function boot()
    {
        //
    }

}