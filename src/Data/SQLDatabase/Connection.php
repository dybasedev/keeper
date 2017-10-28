<?php
/**
 * Connection.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase;


use Illuminate\Contracts\Container\Container;

class Connection
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var PreparationManager
     */
    protected $preparationManager;

    /**
     * 获取预处理查询语句资源管理器
     *
     * @return PreparationManager
     */
    public function getPreparationManager(): PreparationManager
    {
        return $this->preparationManager ?: $this->preparationManager = $this->container['sql.db.preparation'];
    }
}