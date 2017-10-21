<?php
/**
 * Conditioned.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query\Traits;


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

        $this->addStatementStructure('where', [
            'column'   => $columnOrWheres,
            'operator' => $operatorOrValue,
            'value'    => $value,
            'logical'  => $logical,
        ]);

        return $this;
    }

    public function orWhere($columnOrWheres, $operatorOrValue = null, $value = null)
    {
        return $this->where($columnOrWheres, $operatorOrValue, $value, 'or');
    }

    abstract protected function addStatementStructure($key, $structure, $bindings = null);
}