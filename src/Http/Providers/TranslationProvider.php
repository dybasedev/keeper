<?php
/**
 * TranslationProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Providers;


use Dybasedev\Keeper\Http\ContextContainer;
use Dybasedev\Keeper\Http\ModuleProvider;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

class TranslationProvider extends ModuleProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerLoader();

        $this->container->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $this->config['global.locale'];

            $trans = new Translator($loader, $locale);

            $trans->setFallback($this->config['global.fallback_locale']);

            return $trans;
        });
    }

    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        $this->container->singleton('translation.loader', function (ContextContainer $container) {
            return new FileLoader(
                $container['files'],
                $container->applicationPath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang'
            );
        });
    }

    public function alias()
    {
        return [
            'translator' => [
                \Illuminate\Translation\Translator::class,
                \Illuminate\Contracts\Translation\Translator::class,
            ],
        ];
    }

    public function boot()
    {

    }
}