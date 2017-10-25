<?php
/**
 * JoinClause.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query\Statements;


use Dybasedev\Keeper\Data\SQLDatabase\Query\AliasExpression;
use Dybasedev\Keeper\Data\SQLDatabase\Query\Expression;
use Dybasedev\Keeper\Data\SQLDatabase\Query\Grammar;

class JoinClause extends Base
{
    public function table($table, $alias = null)
    {
        if ($table instanceof AliasExpression) {
            if ($table->statement instanceof Base) {
                $bindings = $table->statement->getBindings(true);
            }

            $actualTable = $table->getAlias();
            $table       = $table->getExpressionWithoutAlias();
        }

        if ($table instanceof Base) {
            $bindings = $table->getBindings(true);
            $table    = new Expression($table);
        }

        if ($alias) {
            $actualTable = $alias;
        }

        if (isset($bindings) && count($bindings)) {
            $this->addStatementStructure('join', 'table', ['table' => $table, 'alias' => $actualTable ?? null],
                $bindings);
        } else {
            $this->addStatementStructureWithoutBindings('join', 'table',
                ['table' => $table, 'alias' => $actualTable ?? null]);
        }

        return $this;
    }

    public function type($type)
    {
        if (!in_array($type, ['left-join', 'right-join', 'join', 'inner-join'])) {
            throw new \InvalidArgumentException();
        }

        $this->addStatementStructureWithoutBindings('join-mode', 'join', ['type' => $type]);

        return $this;
    }

    public function on($condition, $operatorOrTarget = null, $target = null, $logical = 'and')
    {
        if ($condition instanceof \Closure) {
            return $this->onNested($condition, $logical);
        }

        if (is_null($target)) {
            return $this->on($condition, '=', $operatorOrTarget);
        }

        $this->addStatementStructureWithoutBindings('join-on', 'join-on', [
            'logical'   => $logical,
            'condition' => $condition,
            'operator'  => $operatorOrTarget,
            'target'    => $target,
        ]);

        return $this;
    }

    public function onNested(\Closure $nested, $logical = 'and')
    {
        $this->addStatementStructure('join-on', 'nested-open', ['logical' => $logical]);
        $nested($this);
        $this->addStatementStructure('join-on', 'nested-close');

        return $this;
    }

    public function orOnNested(\Closure $nested)
    {
        return $this->onNested($nested, 'or');
    }

    public function orOn($condition, $operatorOrTarget = null, $target = null)
    {
        return $this->on($condition, $operatorOrTarget, $target);
    }

    public function buildSql()
    {
        return $this->getGrammar()->exportSql(Grammar::TYPE_JOIN);
    }
}