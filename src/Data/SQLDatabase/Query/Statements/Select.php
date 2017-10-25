<?php
/**
 * Select.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query\Statements;

use Dybasedev\Keeper\Data\SQLDatabase\Query\Grammar;
use Dybasedev\Keeper\Data\SQLDatabase\Query\Grammars\MySQL;
use Dybasedev\Keeper\Data\SQLDatabase\Query\Statements\Traits\Conditioned;

class Select extends Base
{
    use Conditioned;

    public function select($columns = ['*'])
    {

    }

    public function buildSql()
    {
        return (new MySQL($this->structure))->exportSql(Grammar::TYPE_SELECT);
    }
}