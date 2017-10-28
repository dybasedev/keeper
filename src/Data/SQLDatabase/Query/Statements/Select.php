<?php
/**
 * Select.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query\Statements;

use Dybasedev\Keeper\Data\SQLDatabase\Query\Grammar;
use Dybasedev\Keeper\Data\SQLDatabase\Query\Statements\Traits\Conditioned;
use Dybasedev\Keeper\Data\SQLDatabase\Query\Statements\Traits\Joinable;

class Select extends Base
{
    use Conditioned, Joinable;

    public function select($columns = ['*'])
    {
        return $this;
    }

    public function groupBy()
    {
        return $this;
    }

    public function orderBy()
    {
        return $this;
    }

    public function buildSql()
    {
        return $this->getGrammar()->exportSql(Grammar::TYPE_SELECT);
    }
}