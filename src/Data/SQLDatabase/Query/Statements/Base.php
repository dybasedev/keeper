<?php
/**
 * Base.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query\Statements;


use Dybasedev\Keeper\Data\SQLDatabase\Query\Expression;
use Dybasedev\Keeper\Data\SQLDatabase\Query\Grammars\MySQL;
use Illuminate\Support\Arr;

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
     * @var Base
     */
    protected $parentStatement;

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

    public function addStatementStructure($key, $command, $parameters = null, $bindings = null)
    {
        $this->addStatementStructureWithoutBindings($key, $command, $parameters);
        if (is_array($bindings)) {
            $this->bindings[$key] = array_merge($this->bindings[$key] ?? [], $bindings);
        } else {
            $this->bindings[$key][]  = $bindings;
        }

        return $this;
    }

    public function addStatementStructureWithoutBindings($key, $command, $parameters = null)
    {
        $this->structure[$key][] = [$command, $parameters];
        return $this;
    }

    /**
     * @return string
     */
    public function buildSql()
    {
        return '';
    }

    public function getBindings($flat = false)
    {
        if ($flat) {
            return Arr::flatten($this->bindings);
        }

        return $this->bindings;
    }

    public function getGrammar()
    {
        return new MySQL($this->structure);
    }

    public function hasParentStatement()
    {
        return !is_null($this->parentStatement);
    }

    public function withParentStatement($statement)
    {
        $this->parentStatement = $statement;
        return $this;
    }
}