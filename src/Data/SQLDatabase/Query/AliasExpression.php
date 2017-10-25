<?php
/**
 * AliasExpression.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query;


class AliasExpression extends Expression
{
    protected $alias;

    public function alias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return mixed
     */
    public function getExpressionWithoutAlias()
    {
        return parent::getExpression();
    }

    public function getExpression()
    {
        $expression = parent::getExpression();
        return "( {$expression} ) as {$this->alias}";
    }


}