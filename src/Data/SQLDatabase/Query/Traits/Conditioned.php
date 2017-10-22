<?php
/**
 * Conditioned.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query\Traits;


use Closure;

trait Conditioned
{
    public function where($columnOrWheres, $operatorOrValue = null, $value = null, $logical = 'and')
    {
        if (is_null($value)) {
            return $this->where($columnOrWheres, '=', $value);
        }

        if (is_array($columnOrWheres)) {
            foreach ($columnOrWheres as $column => $value) {
                $this->where($column, '=', $value);
            }

            return $this;
        }

        if ($columnOrWheres instanceof Closure) {
            return $this->whereNested($columnOrWheres, $logical);
        }

        $this->addStatementStructure('where', [
            'type'    => 'condition',
            'logical' => $logical,
            'body'    => [
                'column'   => $columnOrWheres,
                'operator' => $operatorOrValue,
                'value'    => '?',
            ],
        ], $value);

        return $this;
    }

    public function whereNested(Closure $nested, $logical)
    {
        $this->addStatementStructure('where', [
            'type'    => 'nested',
            'logical' => $logical,
            'body'    => $nested,
        ]);

        return $this;
    }

    public function orWhere($columnOrWheres, $operatorOrValue = null, $value = null)
    {
        return $this->where($columnOrWheres, $operatorOrValue, $value, 'or');
    }

    abstract protected function addStatementStructure($key, $structure, $bindings = null);
}