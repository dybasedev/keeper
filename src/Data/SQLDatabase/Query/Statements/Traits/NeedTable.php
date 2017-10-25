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
            if ($table->statement instanceof Base) {
                $bindings = $table->statement->getBindings(true);
            }

            $actualTable = $table->getAlias();
            $table       = $table->getExpressionWithoutAlias();
        }

        if ($table instanceof BaseStatement) {
            $bindings = $table->getBindings(true);
            $table    = new Expression($table);
        }

        if ($alias) {
            $actualTable = $alias;
        }

        if (isset($bindings) && count($bindings)) {
            $this->addStatementStructure('table', 'table', ['table' => $table, 'alias' => $actualTable ?? null],
                $bindings);
        } else {
            $this->addStatementStructureWithoutBindings('table', 'table',
                ['table' => $table, 'alias' => $actualTable ?? null]);
        }

        return $this;
    }
}