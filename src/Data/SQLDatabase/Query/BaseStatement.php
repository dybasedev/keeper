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
     * @var array
     */
    protected $bindings = [];

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

    protected function addStatementStructure($type, $structure, $bindings = null)
    {
        $this->structure[$type] = $structure;
        $this->bindings[$type] = $bindings;

        return $this;
    }

    public function buildSql()
    {

    }

    public function getBindings()
    {

    }
}