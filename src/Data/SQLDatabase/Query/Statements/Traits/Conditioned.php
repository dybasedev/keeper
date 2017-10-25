<?php
/**
 * Conditioned.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query\Statements\Traits;


use Closure;
use Dybasedev\Keeper\Data\SQLDatabase\Query\Expression;

trait Conditioned
{
    use Base, NeedTable;

    /**
     * @param                  $columnOrWheres
     * @param null             $operatorOrValue
     * @param mixed|Expression $value
     * @param string           $logical
     *
     * @return $this
     */
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
                'value'    => $value instanceof Expression ? $value->getExpression() : '?',
            ],
        ], $value);

        return $this;
    }

    /**
     * @param Closure $nested
     * @param         $logical
     *
     * @return $this
     */
    public function whereNested(Closure $nested, $logical = 'and')
    {
        $this->addStatementStructureWithoutBindings('where', 'nested-open', [
            'logical' => $logical,
        ]);

        $nested($this);

        $this->addStatementStructureWithoutBindings('where', 'nested-close');

        return $this;
    }

    /**
     * @param Closure $nested
     *
     * @return $this
     */
    public function orWhereNested(Closure $nested)
    {
        return $this->whereNested($nested, 'or');
    }

    /**
     * @param      $columnOrWheres
     * @param null $operatorOrValue
     * @param null $value
     *
     * @return $this
     */
    public function orWhere($columnOrWheres, $operatorOrValue = null, $value = null)
    {
        return $this->where($columnOrWheres, $operatorOrValue, $value, 'or');
    }
}