<?php
/**
 * ValidationProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Providers;


use Dybasedev\Keeper\Http\ContextContainer;
use Dybasedev\Keeper\Http\ModuleProvider;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;

class ValidationProvider extends ModuleProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPresenceVerifier();

        $this->registerValidationFactory();
    }

    /**
     * Register the validation factory.
     *
     * @return void
     */
    protected function registerValidationFactory()
    {
        $this->container->singleton('validator', function (ContextContainer $container) {
            $validator = new Factory($container['translator'], $container);

            // The validation presence verifier is responsible for determining the existence of
            // values in a given data collection which is typically a relational database or
            // other persistent data stores. It is used to check for "uniqueness" as well.
            if (isset($container['db'], $container['validation.presence'])) {
                $validator->setPresenceVerifier($container['validation.presence']);
            }

            return $validator;
        });
    }

    /**
     * Register the database presence verifier.
     *
     * @return void
     */
    protected function registerPresenceVerifier()
    {
        $this->container->singleton('validation.presence', function (ContextContainer $container) {
            return new DatabasePresenceVerifier($container['db']);
        });
    }

    public function alias()
    {
        return [
            'validator' => [\Illuminate\Validation\Factory::class, \Illuminate\Contracts\Validation\Factory::class],
        ];
    }

    public function boot()
    {

    }

}