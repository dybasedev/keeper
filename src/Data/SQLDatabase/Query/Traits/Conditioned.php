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
        if ($columnOrWheres instanceof Closure) {
            return $this->whereNested($columnOrWheres, $logical);
        }

        if (is_array($columnOrWheres)) {
            foreach ($columnOrWheres as $column => $value) {
                $this->where($column, '=', $value);
            }

            return $this;
        }

        if (is_null($value)) {
            return $this->where($columnOrWheres, '=', $operatorOrValue);
        }

        $this->addStatementStructure('where', 'condition', [
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
        $this->addStatementStructure('where', 'nested-open', [
            'logical' => $logical,
        ]);

        $nested($this);

        $this->addStatementStructure('where', 'nested-close');

        return $this;
    }

    public function orWhere($columnOrWheres, $operatorOrValue = null, $value = null)
    {
        return $this->where($columnOrWheres, $operatorOrValue, $value, 'or');
    }

    abstract protected function addStatementStructure($key, $type, $structure = null, $bindings = null);
}