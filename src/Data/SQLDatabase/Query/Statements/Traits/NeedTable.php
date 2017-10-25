<?php
/**
 * NeedTable.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase\Query\Statements\Traits;

use Dybasedev\Keeper\Data\SQLDatabase\Query\Expression;
use Dybasedev\Keeper\Data\SQLDatabase\Query\Statements\Base as BaseStatement;
use Dybasedev\Keeper\Data\SQLDatabase\Query\AliasExpression;

trait NeedTable
{
    use Base;

    public function table($table, $alias = null)
    {
        if ($table instanceof AliasExpression) {
            $actualTable = $table->getAlias();
            $table       = $table->getExpressionWithoutAlias();
        }

        if ($table instanceof BaseStatement) {
            $table = new Expression($table);
        }

        if ($alias) {
            $actualTable = $alias;
        }

        $this->addStatementStructure('table', 'table', ['table' => $table, 'alias' => $actualTable ?? null]);

        return $this;
    }
}