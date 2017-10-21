<?php
/**
 * Expression.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query;


class Expression
{
    protected $expression;

    /**
     * Expression constructor.
     *
     * @param $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    public function getExpression()
    {
        return $this->expression;
    }

    public function __toString()
    {
        return $this->expression;
    }
}