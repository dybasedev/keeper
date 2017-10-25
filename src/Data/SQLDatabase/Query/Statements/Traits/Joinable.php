<?php
/**
 * Joinable.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query\Statements\Traits;

use Dybasedev\Keeper\Data\SQLDatabase\Query\Statements\JoinClause;

trait Joinable
{
    use Base;

    public function leftJoin($table, $condition, $operatorOrTarget = null, $target = null)
    {
        return $this->join($table, $condition, $operatorOrTarget, $target, 'left-join');
    }

    public function rightJoin($table, $condition, $operatorOrTarget = null, $target = null)
    {
        return $this->join($table, $condition, $operatorOrTarget, $target, 'right-join');
    }

    public function innerJoin($table, $condition, $operatorOrTarget = null, $target = null)
    {
        return $this->join($table, $condition, $operatorOrTarget, $target, 'inner-join');
    }

    public function join($table, $condition, $operatorOrTarget = null, $target = null, $mode = 'join')
    {
        $joinClause = (new JoinClause())->withParentStatement($this)->type($mode);
        if ($table instanceof \Closure) {
            $table($joinClause);

            $this->addStatementStructure('join', 'statement', ['statement' => $joinClause->buildSql()],
                $joinClause->getBindings(true));

            return $this;
        }

        $joinClause->table($table);

        if ($condition instanceof \Closure) {
            $this->addStatementStructure('join', 'statement', ['statement' => $joinClause->buildSql()],
                $joinClause->getBindings(true));

            return $this;
        }

        $joinClause->on($condition, $operatorOrTarget, $target);
        $this->addStatementStructure('join', 'statement', ['statement' => $joinClause->buildSql()],
            $joinClause->getBindings(true));

        return $this;
    }
}