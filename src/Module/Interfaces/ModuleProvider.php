<?php
/**
 * ModuleProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Module\Interfaces;


use Illuminate\Contracts\Container\Container;

interface ModuleProvider
{
    public function register(Container $container);

    public function mount(Container $container);
}