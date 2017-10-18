<?php
/**
 * DestructibleModuleProvider.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;


abstract class DestructibleModuleProvider extends ModuleProvider
{
    abstract public function destroy();
}