<?php
/**
 * SessionProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Providers;


use Dybasedev\Keeper\Http\ModuleProvider;
use Dybasedev\Keeper\Http\Session\Manager;

class SessionProvider extends ModuleProvider
{
    public function register()
    {
        $this->container->singleton('session', function () {
            $session = new Manager();

            // Session Configuration
            // ...

            return $session;
        });
    }

    public function boot()
    {

    }

}