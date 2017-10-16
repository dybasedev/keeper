<?php
/**
 * ModuleProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;


use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;

abstract class ModuleProvider
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * ModuleProvider constructor.
     *
     * @param Container  $container
     * @param Repository $config
     */
    public function __construct(Container $container, Repository $config)
    {
        $this->container = $container;
        $this->config    = $config;
    }

    abstract public function register();

    abstract public function boot();
}