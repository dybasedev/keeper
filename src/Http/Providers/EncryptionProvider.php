<?php
/**
 * EncryptionProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Providers;


use Dybasedev\Keeper\Http\ModuleProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterInteface;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;
use RuntimeException;

class EncryptionProvider extends ModuleProvider
{
    public function register()
    {
        $this->container->singleton('encrypter', function (Container $container) {
            $config = $container->make('config')->get('global');

            // If the key starts with "base64:", we will need to decode the key before handing
            // it off to the encrypter. Keys may be base-64 encoded for presentation and we
            // want to make sure to convert them back to the raw bytes before encrypting.
            if (Str::startsWith($key = $this->key($config), 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }

            return new Encrypter($key, $config['cipher']);
        });

        $this->container->alias('encrypter', Encrypter::class);
        $this->container->alias('encrypter', EncrypterInteface::class);
    }

    public function boot()
    {
        //
    }

    /**
     * Extract the encryption key from the given configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function key(array $config)
    {
        return tap($config['key'], function ($key) {
            if (empty($key)) {
                throw new RuntimeException(
                    'No application encryption key has been specified.'
                );
            }
        });
    }

}