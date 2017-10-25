<?php
/**
 * Base.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query\Statements;


use Dybasedev\Keeper\Data\SQLDatabase\Query\Expression;

class Base
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


    protected $grammar;

    protected function addStatementStructure($key, $command, $parameters = null, $bindings = null)
    {
        $this->structure[$key][] = [$command, $parameters];
        if (!in_array($command, ['nested-open', 'nested-close', 'table'])) {
            $this->bindings[$key][]  = $bindings;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function buildSql()
    {
        return '';
    }

    public function getBindings()
    {
        return $this->bindings;
    }
}