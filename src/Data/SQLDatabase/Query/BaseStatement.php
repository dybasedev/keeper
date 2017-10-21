<?php
/**
 * BaseStatement.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query;


class BaseStatement
{
    /**
     * @var string|Expression
     */
    protected $table;

    /**
     * @var array
     */
    protected $structure = [];

    /**
     * @param string|Expression $table
     *
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;

        return $this;
    }
}